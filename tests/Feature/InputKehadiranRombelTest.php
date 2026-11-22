<?php

namespace Tests\Feature;

use App\Filament\Pages\InputKehadiranRombel;
use App\Models\AcademicYear;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InputKehadiranRombelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guru_dapat_menyimpan_kehadiran_massal_untuk_rombelnya(): void
    {
        $data = $this->makeContext();
        $guru = $this->makeUserWithRole('guru', [
            'View:InputKehadiranRombel',
            'Create:Attendance',
            'Update:Attendance',
        ]);
        $data['teacher']->update(['user_id' => $guru->id]);

        $this->actingAs($guru);

        Livewire::test(InputKehadiranRombel::class)
            ->set('attendanceSessionId', $data['session']->id)
            ->set('statuses.'.$data['studentA']->id, $data['hadir']->id)
            ->set('statuses.'.$data['studentB']->id, $data['izin']->id)
            ->set('notes.'.$data['studentB']->id, 'Surat dokter')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $data['session']->id,
            'student_id' => $data['studentA']->id,
            'attendance_status_id' => $data['hadir']->id,
        ]);
        $this->assertDatabaseHas('attendances', [
            'attendance_session_id' => $data['session']->id,
            'student_id' => $data['studentB']->id,
            'attendance_status_id' => $data['izin']->id,
            'notes' => 'Surat dokter',
        ]);
    }

    public function test_guru_tidak_bisa_menyimpan_sesi_guru_lain(): void
    {
        $data = $this->makeContext();
        $guruLain = $this->makeUserWithRole('guru', [
            'View:InputKehadiranRombel',
            'Create:Attendance',
            'Update:Attendance',
        ]);

        $this->actingAs($guruLain);

        Livewire::test(InputKehadiranRombel::class)
            ->set('attendanceSessionId', $data['session']->id)
            ->set('statuses.'.$data['studentA']->id, $data['hadir']->id)
            ->call('save');

        $this->assertDatabaseMissing('attendances', [
            'attendance_session_id' => $data['session']->id,
            'student_id' => $data['studentA']->id,
        ]);
    }

    public function test_guru_dapat_membuat_sesi_absensi_dari_modal(): void
    {
        $data = $this->makeContext();
        $guru = $this->makeUserWithRole('guru', [
            'View:InputKehadiranRombel',
            'Create:Attendance',
            'Update:Attendance',
            'Create:AttendanceSession',
        ]);
        $data['teacher']->update(['user_id' => $guru->id]);

        $this->actingAs($guru);

        Livewire::test(InputKehadiranRombel::class)
            ->callAction('createSession', data: [
                'schedule_id' => $data['schedule']->id,
                'date' => '2026-08-10',
                'topic' => 'Aljabar',
                'notes' => 'Pertemuan tambahan',
            ])
            ->assertNotified();

        $this->assertDatabaseHas('attendance_sessions', [
            'schedule_id' => $data['schedule']->id,
            'date' => '2026-08-10 00:00:00',
            'topic' => 'Aljabar',
        ]);
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
        $ta = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'semester_id' => $semester->id,
        ]);
        $schedule = Schedule::create([
            'teaching_assignment_id' => $ta->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
        ]);
        $session = AttendanceSession::create([
            'schedule_id' => $schedule->id,
            'date' => '2026-08-03',
        ]);

        $hadir = AttendanceStatus::create(['code' => 'HDR', 'name' => 'Hadir', 'numeric_value' => 100, 'is_active' => true]);
        $izin = AttendanceStatus::create(['code' => 'IZN', 'name' => 'Izin', 'numeric_value' => 80, 'is_active' => true]);

        $studentA = Student::create(['nis' => '20260001', 'name' => 'Siswa A', 'status' => 'active']);
        $studentB = Student::create(['nis' => '20260002', 'name' => 'Siswa B', 'status' => 'active']);
        foreach ([$studentA, $studentB] as $student) {
            ClassEnrollment::create([
                'student_id' => $student->id,
                'school_class_id' => $class->id,
                'academic_year_id' => $academicYear->id,
                'status' => 'active',
                'enrolled_at' => '2026-07-01',
            ]);
        }

        return compact('teacher', 'session', 'studentA', 'studentB', 'hadir', 'izin', 'schedule');
    }

    /**
     * @param  list<string>  $permissionNames
     */
    private function makeUserWithRole(string $roleName, array $permissionNames): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

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
