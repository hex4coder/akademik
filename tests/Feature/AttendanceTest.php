<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
use App\Models\ClassEnrollment;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\AcademicDataSeeder;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\AttendanceSessionSeeder;
use Database\Seeders\AttendanceStatusSeeder;
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

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_absensi_valid_tersimpan_dengan_relasinya(): void
    {
        $data = $this->makeContext();

        $attendance = Attendance::create([
            'attendance_session_id' => $data['session']->id,
            'student_id' => $data['enrolledStudent']->id,
            'attendance_status_id' => $data['hadir']->id,
        ]);

        $this->assertTrue($attendance->attendanceSession->is($data['session']));
        $this->assertTrue($attendance->student->is($data['enrolledStudent']));
        $this->assertTrue($attendance->attendanceStatus->is($data['hadir']));
        $this->assertSame(1, $data['enrolledStudent']->attendances()->count());
    }

    public function test_siswa_tidak_terdaftar_aktif_ditolak(): void
    {
        $data = $this->makeContext();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('tidak terdaftar aktif');

        Attendance::create([
            'attendance_session_id' => $data['session']->id,
            'student_id' => $data['otherStudent']->id,
            'attendance_status_id' => $data['hadir']->id,
        ]);
    }

    public function test_satu_siswa_tidak_boleh_absen_ganda_di_satu_pertemuan(): void
    {
        $data = $this->makeContext();

        $attributes = [
            'attendance_session_id' => $data['session']->id,
            'student_id' => $data['enrolledStudent']->id,
            'attendance_status_id' => $data['hadir']->id,
        ];

        Attendance::create($attributes);

        $this->expectException(QueryException::class);

        Attendance::create($attributes);
    }

    public function test_status_tidak_aktif_ditolak(): void
    {
        $data = $this->makeContext();
        $inactive = AttendanceStatus::create([
            'code' => 'TIDAK_AKTIF',
            'name' => 'Status Lama',
            'numeric_value' => 50,
            'is_active' => false,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('tidak aktif');

        Attendance::create([
            'attendance_session_id' => $data['session']->id,
            'student_id' => $data['enrolledStudent']->id,
            'attendance_status_id' => $inactive->id,
        ]);
    }

    public function test_seeder_absensi_idempoten(): void
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
        $this->seed(AttendanceStatusSeeder::class);
        $this->seed(AttendanceSessionSeeder::class);
        $this->seed(AttendanceSeeder::class);

        $count = Attendance::count();
        $this->assertGreaterThan(0, $count);

        $this->seed(AttendanceSeeder::class);

        $this->assertSame($count, Attendance::count());
    }

    public function test_halaman_buat_absensi_dapat_dibuka_oleh_admin(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        foreach (['ViewAny:Attendance', 'Create:Attendance'] as $permissionName) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->get('/admin/attendances/create')->assertOk();
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
        ]);
        $session = AttendanceSession::create([
            'schedule_id' => $schedule->id,
            'date' => '2026-07-20',
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

        $hadir = AttendanceStatus::create([
            'code' => 'HDR',
            'name' => 'Hadir',
            'numeric_value' => 100,
            'is_active' => true,
        ]);

        return compact('session', 'enrolledStudent', 'otherStudent', 'hadir');
    }
}
