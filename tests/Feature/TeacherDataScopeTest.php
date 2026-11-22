<?php

namespace Tests\Feature;

use App\Filament\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Resources\AssignmentScores\Pages\ListAssignmentScores;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\AttendanceSessions\Pages\ListAttendanceSessions;
use App\Filament\Resources\ClassEnrollments\Pages\ListClassEnrollments;
use App\Filament\Resources\HomeroomAssignments\Pages\ListHomeroomAssignments;
use App\Filament\Resources\OwnTeachingDataScope;
use App\Filament\Resources\Schedules\Pages\ListSchedules;
use App\Filament\Resources\SchoolClasses\Pages\ListSchoolClasses;
use App\Filament\Resources\TeachingAssignments\Pages\ListTeachingAssignments;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentScore;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
use App\Models\ClassEnrollment;
use App\Models\GradeLevel;
use App\Models\HomeroomAssignment;
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

class TeacherDataScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guru_hanya_melihat_kelas_dan_penugasan_mengajar_sendiri(): void
    {
        $data = $this->makeContext();

        $this->actingAs($data['teacherAUser']);

        Livewire::test(ListSchoolClasses::class)
            ->assertCanSeeTableRecords([$data['classA']])
            ->assertCanNotSeeTableRecords([$data['classB']]);

        Livewire::test(ListTeachingAssignments::class)
            ->assertCanSeeTableRecords([$data['teachingAssignmentA']])
            ->assertCanNotSeeTableRecords([$data['teachingAssignmentB']]);

        Livewire::test(ListHomeroomAssignments::class)
            ->assertCanSeeTableRecords([$data['homeroomA']])
            ->assertCanNotSeeTableRecords([$data['homeroomB']]);

        Livewire::test(ListClassEnrollments::class)
            ->assertCanSeeTableRecords([$data['enrollmentA']])
            ->assertCanNotSeeTableRecords([$data['enrollmentB']]);
    }

    public function test_guru_hanya_melihat_jadwal_pertemuan_dan_absensi_sendiri(): void
    {
        $data = $this->makeContext();

        $this->actingAs($data['teacherAUser']);

        Livewire::test(ListSchedules::class)
            ->assertCanSeeTableRecords([$data['scheduleA']])
            ->assertCanNotSeeTableRecords([$data['scheduleB']]);

        Livewire::test(ListAttendanceSessions::class)
            ->assertCanSeeTableRecords([$data['sessionA']])
            ->assertCanNotSeeTableRecords([$data['sessionB']]);

        Livewire::test(ListAttendances::class)
            ->assertCanSeeTableRecords([$data['attendanceA']])
            ->assertCanNotSeeTableRecords([$data['attendanceB']]);
    }

    public function test_guru_hanya_melihat_tugas_dan_nilai_sendiri(): void
    {
        $data = $this->makeContext();

        $this->actingAs($data['teacherAUser']);

        Livewire::test(ListAssignments::class)
            ->assertCanSeeTableRecords([$data['assignmentA']])
            ->assertCanNotSeeTableRecords([$data['assignmentB']]);

        Livewire::test(ListAssignmentScores::class)
            ->assertCanSeeTableRecords([$data['scoreA']])
            ->assertCanNotSeeTableRecords([$data['scoreB']]);
    }

    public function test_admin_melihat_semua_data(): void
    {
        $data = $this->makeContext();

        $this->actingAs($this->makeUserWithRole('admin'));

        Livewire::test(ListAssignments::class)
            ->assertCanSeeTableRecords([$data['assignmentA'], $data['assignmentB']]);

        Livewire::test(ListAssignmentScores::class)
            ->assertCanSeeTableRecords([$data['scoreA'], $data['scoreB']]);
    }

    public function test_filter_rombel_dan_mapel_tersedia_dan_berfungsi(): void
    {
        $data = $this->makeContext();

        $this->actingAs($this->makeUserWithRole('admin'));

        Livewire::test(ListAssignments::class)
            ->assertTableFilterExists('school_class_id')
            ->assertTableFilterExists('subject_id')
            ->filterTable('school_class_id', $data['classB']->id)
            ->assertCanSeeTableRecords([$data['assignmentB']])
            ->assertCanNotSeeTableRecords([$data['assignmentA']]);

        Livewire::test(ListAssignments::class)
            ->filterTable('subject_id', $data['subjectB']->id)
            ->assertCanSeeTableRecords([$data['assignmentB']])
            ->assertCanNotSeeTableRecords([$data['assignmentA']]);

        Livewire::test(ListAssignmentScores::class)
            ->assertTableFilterExists('school_class_id')
            ->assertTableFilterExists('subject_id')
            ->filterTable('school_class_id', $data['classA']->id)
            ->assertCanSeeTableRecords([$data['scoreA']])
            ->assertCanNotSeeTableRecords([$data['scoreB']]);

        Livewire::test(ListAssignmentScores::class)
            ->filterTable('subject_id', $data['subjectA']->id)
            ->assertCanSeeTableRecords([$data['scoreA']])
            ->assertCanNotSeeTableRecords([$data['scoreB']]);
    }

    public function test_pilihan_filter_rombel_dan_mapel_juga_dibatasi(): void
    {
        $data = $this->makeContext();

        $this->actingAs($data['teacherAUser']);

        $this->assertSame(
            [$data['classA']->id],
            OwnTeachingDataScope::apply(SchoolClass::query(), 'teachingAssignments')->pluck('id')->all()
        );
        $this->assertSame(
            [$data['subjectA']->id],
            OwnTeachingDataScope::apply(Subject::query(), 'teachingAssignments')->pluck('id')->all()
        );
    }

    public function test_guru_tanpa_profil_tidak_melihat_data_apapun(): void
    {
        $data = $this->makeContext();

        $user = User::factory()->create();
        $user->assignRole($data['guruRole']);

        $this->actingAs($user);

        Livewire::test(ListAssignments::class)
            ->assertCanNotSeeTableRecords([$data['assignmentA'], $data['assignmentB']]);
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

        $classA = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);
        $classB = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 2,
            'name' => 'X TJKT 2',
        ]);

        $subjectA = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $subjectB = Subject::create(['code' => 'BIN', 'name' => 'Bahasa Indonesia']);

        $guruRole = Role::firstOrCreate([
            'name' => 'guru',
            'guard_name' => 'web',
        ]);

        foreach ([
            'ViewAny:SchoolClass',
            'ViewAny:TeachingAssignment',
            'ViewAny:HomeroomAssignment',
            'ViewAny:ClassEnrollment',
            'ViewAny:Schedule',
            'ViewAny:AttendanceSession',
            'ViewAny:Attendance',
            'ViewAny:Assignment',
            'ViewAny:AssignmentScore',
        ] as $permissionName) {
            $guruRole->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $teacherA = Teacher::create(['name' => 'Guru A', 'nip' => '111111111111111111']);
        $teacherB = Teacher::create(['name' => 'Guru B', 'nip' => '222222222222222222']);

        $teacherAUser = User::factory()->create();
        $teacherAUser->assignRole($guruRole);
        $teacherA->update(['user_id' => $teacherAUser->id]);

        $teacherBUser = User::factory()->create();
        $teacherBUser->assignRole($guruRole);
        $teacherB->update(['user_id' => $teacherBUser->id]);

        $teachingAssignmentA = TeachingAssignment::create([
            'teacher_id' => $teacherA->id,
            'subject_id' => $subjectA->id,
            'school_class_id' => $classA->id,
            'semester_id' => $semester->id,
        ]);
        $teachingAssignmentB = TeachingAssignment::create([
            'teacher_id' => $teacherB->id,
            'subject_id' => $subjectB->id,
            'school_class_id' => $classB->id,
            'semester_id' => $semester->id,
        ]);

        $homeroomA = HomeroomAssignment::create([
            'teacher_id' => $teacherA->id,
            'school_class_id' => $classA->id,
        ]);
        $homeroomB = HomeroomAssignment::create([
            'teacher_id' => $teacherB->id,
            'school_class_id' => $classB->id,
        ]);

        $studentA = Student::create([
            'nis' => '20260001',
            'name' => 'Siswa Satu',
            'status' => 'active',
        ]);
        $studentB = Student::create([
            'nis' => '20260002',
            'name' => 'Siswa Dua',
            'status' => 'active',
        ]);

        $enrollmentA = ClassEnrollment::create([
            'student_id' => $studentA->id,
            'school_class_id' => $classA->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
            'enrolled_at' => '2026-07-01',
        ]);
        $enrollmentB = ClassEnrollment::create([
            'student_id' => $studentB->id,
            'school_class_id' => $classB->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
            'enrolled_at' => '2026-07-01',
        ]);

        $scheduleA = Schedule::create([
            'teaching_assignment_id' => $teachingAssignmentA->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'Ruang 1',
        ]);
        $scheduleB = Schedule::create([
            'teaching_assignment_id' => $teachingAssignmentB->id,
            'day_of_week' => 'Selasa',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'Ruang 2',
        ]);

        $sessionA = AttendanceSession::create([
            'schedule_id' => $scheduleA->id,
            'date' => '2026-08-03',
        ]);
        $sessionB = AttendanceSession::create([
            'schedule_id' => $scheduleB->id,
            'date' => '2026-08-04',
        ]);

        $status = AttendanceStatus::create([
            'code' => 'HDR',
            'name' => 'Hadir',
            'numeric_value' => 100,
            'is_active' => true,
        ]);

        $attendanceA = Attendance::create([
            'attendance_session_id' => $sessionA->id,
            'student_id' => $studentA->id,
            'attendance_status_id' => $status->id,
        ]);
        $attendanceB = Attendance::create([
            'attendance_session_id' => $sessionB->id,
            'student_id' => $studentB->id,
            'attendance_status_id' => $status->id,
        ]);

        $assignmentA = Assignment::create([
            'teaching_assignment_id' => $teachingAssignmentA->id,
            'title' => 'Tugas Guru A',
            'due_date' => '2026-08-15',
            'max_score' => 100,
        ]);
        $assignmentB = Assignment::create([
            'teaching_assignment_id' => $teachingAssignmentB->id,
            'title' => 'Tugas Guru B',
            'due_date' => '2026-08-16',
            'max_score' => 100,
        ]);

        $scoreA = AssignmentScore::create([
            'assignment_id' => $assignmentA->id,
            'student_id' => $studentA->id,
            'score' => 85,
        ]);
        $scoreB = AssignmentScore::create([
            'assignment_id' => $assignmentB->id,
            'student_id' => $studentB->id,
            'score' => 90,
        ]);

        $this->makeUserWithRole('admin');

        return compact(
            'academicYear',
            'semester',
            'classA',
            'classB',
            'subjectA',
            'subjectB',
            'guruRole',
            'teacherA',
            'teacherB',
            'teacherAUser',
            'teacherBUser',
            'teachingAssignmentA',
            'teachingAssignmentB',
            'homeroomA',
            'homeroomB',
            'enrollmentA',
            'enrollmentB',
            'scheduleA',
            'scheduleB',
            'sessionA',
            'sessionB',
            'attendanceA',
            'attendanceB',
            'assignmentA',
            'assignmentB',
            'scoreA',
            'scoreB',
        );
    }

    private function makeUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $permissionNames = $roleName === 'admin'
            ? ['ViewAny:Assignment', 'ViewAny:AssignmentScore']
            : [];

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
