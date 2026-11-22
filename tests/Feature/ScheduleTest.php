<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\AcademicDataSeeder;
use Database\Seeders\ScheduleSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\TeachingAssignmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_jadwal_valid_tersimpan_dengan_relasinya(): void
    {
        $data = $this->makeContext();

        $schedule = Schedule::create([
            'teaching_assignment_id' => $data['assignmentA']->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
            'room' => 'R. 101',
        ]);

        $this->assertTrue($schedule->teachingAssignment->is($data['assignmentA']));
        $this->assertSame(1, $data['assignmentA']->schedules()->count());
    }

    public function test_waktu_selesai_harus_setelah_waktu_mulai(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Waktu selesai harus setelah waktu mulai.');

        Schedule::create([
            'teaching_assignment_id' => $data['assignmentA']->id,
            'day_of_week' => 'Senin',
            'start_time' => '09:00:00',
            'end_time' => '07:30:00',
        ]);
    }

    public function test_jadwal_yang_bertabrakan_di_kelas_sama_ditolak(): void
    {
        $data = $this->makeContext();

        Schedule::create([
            'teaching_assignment_id' => $data['assignmentA']->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('bertabrakan');

        Schedule::create([
            'teaching_assignment_id' => $data['assignmentC']->id,
            'day_of_week' => 'Senin',
            'start_time' => '08:30:00',
            'end_time' => '10:00:00',
        ]);
    }

    public function test_jadwal_kelas_berbeda_pada_waktu_sama_dibolehkan(): void
    {
        $data = $this->makeContext();

        foreach ([$data['assignmentA'], $data['assignmentB']] as $assignment) {
            Schedule::create([
                'teaching_assignment_id' => $assignment->id,
                'day_of_week' => 'Senin',
                'start_time' => '07:30:00',
                'end_time' => '09:00:00',
            ]);
        }

        $this->assertSame(2, Schedule::count());
    }

    public function test_jadwal_identik_ditolak(): void
    {
        $data = $this->makeContext();

        $attributes = [
            'teaching_assignment_id' => $data['assignmentA']->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
        ];

        Schedule::create($attributes);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('bertabrakan');

        Schedule::create($attributes);
    }

    public function test_seeder_mengisi_jadwal_secara_idempoten(): void
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

        $this->seed(SubjectSeeder::class);
        $this->seed(AcademicDataSeeder::class);
        $this->seed(TeacherSeeder::class);
        $this->seed(TeachingAssignmentSeeder::class);

        // BIN tidak masuk seeder penugasan, jadi dibuat di sini agar kelima baris jadwal terisi.
        TeachingAssignment::firstOrCreate([
            'teacher_id' => Teacher::where('nip', '199603142022211001')->firstOrFail()->id,
            'subject_id' => Subject::where('code', 'BIN')->firstOrFail()->id,
            'school_class_id' => SchoolClass::where('name', 'XI TJKT 1')->firstOrFail()->id,
            'semester_id' => Semester::where('name', 'Ganjil')->firstOrFail()->id,
        ]);

        $this->seed(ScheduleSeeder::class);
        $this->seed(ScheduleSeeder::class);

        $this->assertSame(5, Schedule::count());
        $this->assertDatabaseHas('schedules', [
            'day_of_week' => 'Selasa',
            'start_time' => '07:30:00',
            'room' => 'Lab. TJKT',
        ]);
    }

    public function test_halaman_buat_jadwal_dapat_dibuka_oleh_admin(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        foreach (['ViewAny:Schedule', 'Create:Schedule'] as $permissionName) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->get('/admin/schedules/create')->assertOk();
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
        $gradeX = GradeLevel::create(['name' => 'X']);
        $gradeXI = GradeLevel::create(['name' => 'XI']);
        $majorTjkt = Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer']);

        $classX = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeX->id,
            'major_id' => $majorTjkt->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);
        $classXI = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeXI->id,
            'major_id' => $majorTjkt->id,
            'group_number' => 1,
            'name' => 'XI TJKT 1',
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
        $teacher = Teacher::create(['name' => 'Guru A', 'nip' => '111111111111111111']);

        $subjectA = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $subjectB = Subject::create(['code' => 'BIN', 'name' => 'Bahasa Indonesia']);
        $subjectC = Subject::create(['code' => 'PJOK', 'name' => 'Penjaskes']);

        $makeAssignment = fn (Subject $subject, SchoolClass $schoolClass): TeachingAssignment => TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'semester_id' => $semester->id,
        ]);

        $assignmentA = $makeAssignment($subjectA, $classX);
        $assignmentB = $makeAssignment($subjectB, $classXI);
        $assignmentC = $makeAssignment($subjectC, $classX);

        return compact('assignmentA', 'assignmentB', 'assignmentC');
    }
}
