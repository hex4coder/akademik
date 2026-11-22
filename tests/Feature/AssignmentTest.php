<?php

namespace Tests\Feature;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Filament\Resources\AssignmentScores\AssignmentScoreResource;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentScore;
use App\Models\ClassEnrollment;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\AcademicDataSeeder;
use Database\Seeders\AssignmentScoreSeeder;
use Database\Seeders\AssignmentSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\TeachingAssignmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_tugas_valid_tersimpan_dengan_relasinya(): void
    {
        $data = $this->makeContext();

        $tugas = Assignment::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'title' => 'Tugas Matematika',
            'due_date' => '2026-08-15',
            'max_score' => 100,
        ]);

        $this->assertTrue($tugas->teachingAssignment->is($data['teachingAssignment']));
        $this->assertTrue($data['teachingAssignment']->assignments()->whereKey($tugas->id)->exists());
    }

    public function test_tenggat_tugas_di_luar_rentang_semester_ditolak(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('rentang semester');

        Assignment::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'title' => 'Tugas di luar semester',
            'due_date' => '2027-01-15',
            'max_score' => 100,
        ]);
    }

    public function test_nilai_maksimal_tugas_harus_lebih_besar_dari_nol(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('lebih besar dari nol');

        Assignment::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'title' => 'Tugas tanpa nilai maksimal',
            'max_score' => 0,
        ]);
    }

    public function test_nilai_tugas_valid_tersimpan_dengan_relasinya(): void
    {
        $data = $this->makeContext();

        $nilai = AssignmentScore::create([
            'assignment_id' => $data['tugas']->id,
            'student_id' => $data['enrolledStudent']->id,
            'score' => 85,
        ]);

        $this->assertTrue($nilai->assignment->is($data['tugas']));
        $this->assertTrue($nilai->student->is($data['enrolledStudent']));
        $this->assertSame(1, $data['enrolledStudent']->assignmentScores()->count());
    }

    public function test_nilai_melebihi_nilai_maksimal_ditolak(): void
    {
        $data = $this->makeContext();
        $tugas = Assignment::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'title' => 'Tugas dengan nilai maksimal 50',
            'max_score' => 50,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('antara 0 dan 50');

        AssignmentScore::create([
            'assignment_id' => $tugas->id,
            'student_id' => $data['enrolledStudent']->id,
            'score' => 60,
        ]);
    }

    public function test_siswa_tidak_terdaftar_aktif_ditolak(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('tidak terdaftar aktif');

        AssignmentScore::create([
            'assignment_id' => $data['tugas']->id,
            'student_id' => $data['otherStudent']->id,
            'score' => 80,
        ]);
    }

    public function test_satu_siswa_tidak_boleh_dinilai_ganda_di_tugas_yang_sama(): void
    {
        $data = $this->makeContext();
        $attributes = [
            'assignment_id' => $data['tugas']->id,
            'student_id' => $data['enrolledStudent']->id,
            'score' => 80,
        ];

        AssignmentScore::create($attributes);

        $this->expectException(QueryException::class);

        AssignmentScore::create($attributes);
    }

    public function test_seeder_tugas_dan_nilai_idempoten(): void
    {
        $this->makeSeedPrerequisites();
        $this->seed(SubjectSeeder::class);
        $this->seed(AcademicDataSeeder::class);
        $this->seed(TeacherSeeder::class);
        $this->seed(TeachingAssignmentSeeder::class);
        $this->seed(AssignmentSeeder::class);
        $this->seed(AssignmentScoreSeeder::class);

        $tugasCount = Assignment::count();
        $nilaiCount = AssignmentScore::count();

        $this->assertGreaterThan(0, $tugasCount);
        $this->assertGreaterThan(0, $nilaiCount);

        $this->seed(AssignmentSeeder::class);
        $this->seed(AssignmentScoreSeeder::class);

        $this->assertSame($tugasCount, Assignment::count());
        $this->assertSame($nilaiCount, AssignmentScore::count());
    }

    public function test_admin_dapat_membuka_halaman_buat_tugas_dan_nilai(): void
    {
        $admin = $this->makeUserWithRole('admin', [
            'ViewAny:Assignment',
            'Create:Assignment',
            'ViewAny:AssignmentScore',
            'Create:AssignmentScore',
        ]);

        $this->actingAs($admin)->get(AssignmentResource::getUrl('create'))->assertOk();
        $this->actingAs($admin)->get(AssignmentScoreResource::getUrl('create'))->assertOk();
    }

    public function test_guru_hanya_bisa_lihat_tugas(): void
    {
        $guru = $this->makeUserWithRole('guru', [
            'ViewAny:Assignment',
            'View:Assignment',
        ]);

        $this->assertTrue($guru->can('viewAny', Assignment::class));
        $this->assertFalse($guru->can('create', Assignment::class));

        $this->actingAs($guru)->get(AssignmentResource::getUrl('index'))->assertOk();
        $this->actingAs($guru)->get(AssignmentResource::getUrl('create'))->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function makeContext(): array
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
        $grade = GradeLevel::create(['name' => 'X']);
        $major = Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer']);
        $class = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);
        $teacher = Teacher::create(['name' => 'Guru A', 'nip' => '111111111111111111']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);

        $teachingAssignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'semester_id' => $semester->id,
        ]);

        $enrolledStudent = Student::create([
            'nis' => '20260001',
            'name' => 'Siswa Satu',
            'gender' => 'L',
            'status' => 'active',
        ]);
        ClassEnrollment::create([
            'student_id' => $enrolledStudent->id,
            'school_class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
            'enrolled_at' => '2026-07-01',
        ]);
        $otherStudent = Student::create([
            'nis' => '20260002',
            'name' => 'Siswa Dua',
            'gender' => 'P',
            'status' => 'active',
        ]);

        $tugas = Assignment::create([
            'teaching_assignment_id' => $teachingAssignment->id,
            'title' => 'Tugas Matematika',
            'due_date' => '2026-08-15',
            'max_score' => 100,
        ]);

        return compact('teachingAssignment', 'enrolledStudent', 'otherStudent', 'tugas');
    }

    private function makeSeedPrerequisites(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        foreach (['X', 'XI', 'XII'] as $gradeName) {
            GradeLevel::create(['name' => $gradeName]);
        }

        foreach (['DKV', 'TJKT', 'MPLB'] as $majorCode) {
            Major::create(['code' => $majorCode, 'name' => "Jurusan {$majorCode}"]);
        }
    }

    /**
     * @param  list<string>  $permissionNames
     */
    private function makeUserWithRole(string $roleName, array $permissionNames): User
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        foreach ($permissionNames as $permissionName) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
