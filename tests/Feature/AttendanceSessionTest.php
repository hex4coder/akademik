<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
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
use Database\Seeders\AttendanceSessionSeeder;
use Database\Seeders\ScheduleSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\TeachingAssignmentSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pertemuan_tersimpan_dengan_relasinya(): void
    {
        $data = $this->makeContext();

        $session = AttendanceSession::create([
            'schedule_id' => $data['schedule']->id,
            'date' => '2026-07-20',
            'topic' => 'Pengenalan jaringan',
        ]);

        $this->assertTrue($session->schedule->is($data['schedule']));
        $this->assertSame('2026-07-20', $session->date->toDateString());
    }

    public function test_satu_jadwal_tidak_boleh_punya_pertemuan_ganda_pada_tanggal_sama(): void
    {
        $data = $this->makeContext();

        $attributes = [
            'schedule_id' => $data['schedule']->id,
            'date' => '2026-07-20',
        ];

        AttendanceSession::create($attributes);

        $this->expectException(QueryException::class);

        AttendanceSession::create($attributes);
    }

    public function test_tanggal_di_luar_rentang_semester_ditolak(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('rentang semester');

        AttendanceSession::create([
            'schedule_id' => $data['schedule']->id,
            'date' => '2027-01-15',
        ]);
    }

    public function test_seeder_membuat_satu_pertemuan_per_jadwal_secara_idempoten(): void
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
        $this->seed(ScheduleSeeder::class);

        $scheduleCount = Schedule::count();

        $this->seed(AttendanceSessionSeeder::class);
        $this->seed(AttendanceSessionSeeder::class);

        $this->assertSame($scheduleCount, AttendanceSession::count());
        $this->assertGreaterThan(0, $scheduleCount);
        $this->assertDatabaseHas('attendance_sessions', ['topic' => 'Pertemuan 1']);
    }

    public function test_halaman_buat_pertemuan_dapat_dibuka_oleh_admin(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        foreach (['ViewAny:AttendanceSession', 'Create:AttendanceSession'] as $permissionName) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->get('/admin/attendance-sessions/create')->assertOk();
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
        $grade = GradeLevel::create(['name' => 'X']);
        $major = Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer']);
        $class = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);
        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
        $teacher = Teacher::create(['name' => 'Guru A', 'nip' => '111111111111111111']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'semester_id' => $semester->id,
        ]);
        $schedule = Schedule::create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
            'room' => 'R. 101',
        ]);

        return compact('schedule');
    }
}
