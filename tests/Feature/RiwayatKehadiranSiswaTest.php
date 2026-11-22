<?php

namespace Tests\Feature;

use App\Filament\Pages\RiwayatKehadiranSiswa;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RiwayatKehadiranSiswaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_halaman_dapat_dibuka_oleh_admin_dan_guru(): void
    {
        $this->actingAs($this->makeUserWithRole('admin'))
            ->get('/admin/riwayat-kehadiran-siswa')
            ->assertOk();

        $this->actingAs($this->makeUserWithRole('guru'))
            ->get('/admin/riwayat-kehadiran-siswa')
            ->assertOk();
    }

    public function test_halaman_ditolak_untuk_pengguna_tanpa_izin(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'tanpa-izin',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/admin/riwayat-kehadiran-siswa')
            ->assertForbidden();
    }

    public function test_semester_aktif_menjadi_pilihan_awal(): void
    {
        $data = $this->makeContext();

        Livewire::test(RiwayatKehadiranSiswa::class)
            ->assertSet('semesterId', $data['semester']->id);
    }

    public function test_rekap_menampilkan_total_poin_rata_rata_dan_rekap_status(): void
    {
        $data = $this->makeContext();

        Livewire::test(RiwayatKehadiranSiswa::class)
            ->set('studentId', $data['student']->id)
            ->set('semesterId', $data['semester']->id)
            ->assertSeeHtml('data-recap="jumlah-pertemuan">2</div>')
            ->assertSeeHtml('data-recap="total-poin">90</div>')
            ->assertSeeHtml('data-recap="rata-rata">45</div>')
            ->assertSeeHtml('data-recap="persentase-kehadiran">50%</div>')
            ->assertSeeHtml('class="fi-ta-table"')
            ->assertSee('Riwayat Kehadiran — Siswa Uji')
            ->assertSee('MTK-RIW')
            ->assertSee('Hadir')
            ->assertSee('Bolos');
    }

    public function test_filter_semester_membatasi_riwayat(): void
    {
        $data = $this->makeContext();

        Livewire::test(RiwayatKehadiranSiswa::class)
            ->set('studentId', $data['student']->id)
            ->set('semesterId', $data['otherSemester']->id)
            ->assertSeeHtml('data-recap="jumlah-pertemuan">1</div>')
            ->assertSee('BIN-RIW')
            ->assertDontSee('MTK-RIW');
    }

    private function makeUserWithRole(string $roleName): User
    {
        $permission = Permission::firstOrCreate([
            'name' => 'View:RiwayatKehadiranSiswa',
            'guard_name' => 'web',
        ]);
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
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
        $otherSemester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Genap',
            'start_date' => '2027-01-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);
        $grade = GradeLevel::create(['name' => 'X']);
        $major = Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer']);
        $schoolClass = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);
        $teacher = Teacher::create(['name' => 'Guru Uji', 'nip' => '999999999999999999']);
        $subject = Subject::create(['code' => 'MTK-RIW', 'name' => 'Matematika']);
        $otherSubject = Subject::create(['code' => 'BIN-RIW', 'name' => 'Bahasa Indonesia']);

        $assignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'semester_id' => $semester->id,
        ]);
        $otherAssignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $otherSubject->id,
            'school_class_id' => $schoolClass->id,
            'semester_id' => $otherSemester->id,
        ]);
        $schedule = Schedule::create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
        ]);
        $otherSchedule = Schedule::create([
            'teaching_assignment_id' => $otherAssignment->id,
            'day_of_week' => 'Selasa',
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
        ]);
        $session = AttendanceSession::create([
            'schedule_id' => $schedule->id,
            'date' => '2026-08-03',
        ]);
        $secondSession = AttendanceSession::create([
            'schedule_id' => $schedule->id,
            'date' => '2026-08-04',
        ]);
        $otherSession = AttendanceSession::create([
            'schedule_id' => $otherSchedule->id,
            'date' => '2027-01-10',
        ]);

        $student = Student::create([
            'nis' => '20260099',
            'name' => 'Siswa Uji',
            'gender' => 'L',
            'status' => 'active',
        ]);
        ClassEnrollment::create([
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
            'enrolled_at' => '2026-07-01',
        ]);

        $hadir = AttendanceStatus::create([
            'code' => 'HDR',
            'name' => 'Hadir',
            'numeric_value' => 100,
            'is_active' => true,
        ]);
        $bolos = AttendanceStatus::create([
            'code' => 'BOL',
            'name' => 'Bolos',
            'numeric_value' => -10,
            'is_active' => true,
        ]);

        Attendance::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'attendance_status_id' => $hadir->id,
        ]);
        Attendance::create([
            'attendance_session_id' => $secondSession->id,
            'student_id' => $student->id,
            'attendance_status_id' => $bolos->id,
        ]);
        Attendance::create([
            'attendance_session_id' => $otherSession->id,
            'student_id' => $student->id,
            'attendance_status_id' => $hadir->id,
        ]);

        return compact('semester', 'otherSemester', 'student');
    }
}
