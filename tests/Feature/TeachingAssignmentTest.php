<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\AcademicDataSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\TeachingAssignmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeachingAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_penugasan_yang_valid_tersimpan_dengan_relasinya(): void
    {
        $data = $this->makeContext();

        $assignment = TeachingAssignment::create([
            'teacher_id' => $data['teacherA']->id,
            'subject_id' => $data['subjectGeneral']->id,
            'school_class_id' => $data['classXTjkt']->id,
            'semester_id' => $data['semesterA']->id,
        ]);

        $this->assertTrue($assignment->teacher->is($data['teacherA']));
        $this->assertTrue($assignment->subject->is($data['subjectGeneral']));
        $this->assertTrue($assignment->schoolClass->is($data['classXTjkt']));
        $this->assertTrue($assignment->semester->is($data['semesterA']));
    }

    public function test_penugasan_identik_ditolak(): void
    {
        $data = $this->makeContext();

        $attributes = [
            'teacher_id' => $data['teacherA']->id,
            'subject_id' => $data['subjectGeneral']->id,
            'school_class_id' => $data['classXTjkt']->id,
            'semester_id' => $data['semesterA']->id,
        ];

        TeachingAssignment::create($attributes);

        $this->expectException(QueryException::class);

        TeachingAssignment::create($attributes);
    }

    public function test_beberapa_guru_boleh_mengajar_mapel_yang_sama(): void
    {
        $data = $this->makeContext();

        foreach ([$data['teacherA'], $data['teacherB']] as $teacher) {
            TeachingAssignment::create([
                'teacher_id' => $teacher->id,
                'subject_id' => $data['subjectGeneral']->id,
                'school_class_id' => $data['classXTjkt']->id,
                'semester_id' => $data['semesterA']->id,
            ]);
        }

        $this->assertSame(2, TeachingAssignment::count());
    }

    public function test_semester_harus_satu_tahun_ajaran_dengan_kelas(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Semester harus berada pada tahun ajaran yang sama dengan kelas.');

        TeachingAssignment::create([
            'teacher_id' => $data['teacherA']->id,
            'subject_id' => $data['subjectGeneral']->id,
            'school_class_id' => $data['classXTjkt']->id,
            'semester_id' => $data['semesterB']->id,
        ]);
    }

    public function test_mapel_khusus_tingkat_tidak_boleh_dipakai_tingkat_lain(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('tingkat kelas lain');

        TeachingAssignment::create([
            'teacher_id' => $data['teacherA']->id,
            'subject_id' => $data['subjectGradeX']->id,
            'school_class_id' => $data['classXIDkv']->id,
            'semester_id' => $data['semesterA']->id,
        ]);
    }

    public function test_mapel_khusus_jurusan_tidak_boleh_dipakai_jurusan_lain(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('jurusan lain');

        TeachingAssignment::create([
            'teacher_id' => $data['teacherA']->id,
            'subject_id' => $data['subjectMajorDkv']->id,
            'school_class_id' => $data['classXTjkt']->id,
            'semester_id' => $data['semesterA']->id,
        ]);
    }

    public function test_halaman_buat_penugasan_dapat_dibuka_oleh_admin(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        foreach (['ViewAny:TeachingAssignment', 'Create:TeachingAssignment'] as $permissionName) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->get('/admin/teaching-assignments/create')->assertOk();
    }

    public function test_seeder_menugaskan_bu_uni_pada_tjkt01_kelas_x(): void
    {
        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $academicYear = AcademicYear::where('name', '2026/2027')->firstOrFail();
        Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        GradeLevel::create(['name' => 'X']);
        GradeLevel::create(['name' => 'XI']);
        GradeLevel::create(['name' => 'XII']);
        Major::create(['code' => 'DKV', 'name' => 'Desain Komunikasi Visual']);
        Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer']);
        Major::create(['code' => 'MPLB', 'name' => 'Manajemen Perkantoran dan Layanan Bisnis']);

        $this->seed(SubjectSeeder::class);
        $this->seed(AcademicDataSeeder::class);
        $this->seed(TeacherSeeder::class);
        $this->seed(TeachingAssignmentSeeder::class);

        $ardan = Teacher::where('nip', '199603142022211001')->firstOrFail();
        $uni = Teacher::where('nip', '199304202024212048')->firstOrFail();
        $dasarTjkt = Subject::where('code', 'TJKT01')->firstOrFail();
        $mkkTjktXi = Subject::where('code', 'MKK-TJKT-XI')->firstOrFail();
        $firstClass = SchoolClass::where('name', 'X TJKT 1')->firstOrFail();
        $secondClass = SchoolClass::where('name', 'XI TJKT 1')->firstOrFail();

        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $uni->id,
            'subject_id' => $dasarTjkt->id,
            'school_class_id' => $firstClass->id,
        ]);

        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $ardan->id,
            'subject_id' => $mkkTjktXi->id,
            'school_class_id' => $secondClass->id,
        ]);

        $this->assertDatabaseMissing('teaching_assignments', [
            'teacher_id' => $ardan->id,
            'subject_id' => $dasarTjkt->id,
            'school_class_id' => $secondClass->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeContext(): array
    {
        $yearA = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $yearB = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        $gradeX = GradeLevel::create(['name' => 'X']);
        $gradeXI = GradeLevel::create(['name' => 'XI']);
        $majorTjkt = Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer']);
        $majorDkv = Major::create(['code' => 'DKV', 'name' => 'Desain Komunikasi Visual']);

        $classXTjkt = SchoolClass::create([
            'academic_year_id' => $yearA->id,
            'grade_level_id' => $gradeX->id,
            'major_id' => $majorTjkt->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);
        $classXIDkv = SchoolClass::create([
            'academic_year_id' => $yearA->id,
            'grade_level_id' => $gradeXI->id,
            'major_id' => $majorDkv->id,
            'group_number' => 1,
            'name' => 'XI DKV 1',
        ]);

        $semesterA = Semester::create([
            'academic_year_id' => $yearA->id,
            'name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
        $semesterB = Semester::create([
            'academic_year_id' => $yearB->id,
            'name' => 'Ganjil',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => false,
        ]);

        $teacherA = Teacher::create(['name' => 'Guru A', 'nip' => '111111111111111111']);
        $teacherB = Teacher::create(['name' => 'Guru B', 'nip' => '222222222222222222']);

        $subjectGeneral = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $subjectGradeX = Subject::create([
            'code' => 'INF',
            'name' => 'Informatika',
            'grade_level_id' => $gradeX->id,
        ]);
        $subjectMajorDkv = Subject::create([
            'code' => 'DKV01',
            'name' => 'Dasar Desain Grafis',
            'major_id' => $majorDkv->id,
        ]);

        return compact(
            'classXTjkt',
            'classXIDkv',
            'semesterA',
            'semesterB',
            'teacherA',
            'teacherB',
            'subjectGeneral',
            'subjectGradeX',
            'subjectMajorDkv'
        );
    }
}
