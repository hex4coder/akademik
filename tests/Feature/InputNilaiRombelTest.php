<?php

namespace Tests\Feature;

use App\Filament\Pages\InputNilaiRombel;
use App\Models\AcademicYear;
use App\Models\Assignment;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InputNilaiRombelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_halaman_dapat_dibuka_oleh_admin(): void
    {
        $admin = $this->makeUserWithRole('admin', ['View:InputNilaiRombel']);

        $this->actingAs($admin)
            ->get(InputNilaiRombel::getUrl())
            ->assertOk();
    }

    public function test_guru_dapat_menyimpan_nilai_massal_untuk_rombelnya(): void
    {
        $data = $this->makeContext();
        $guru = $this->makeUserWithRole('guru', [
            'View:InputNilaiRombel',
            'ViewAny:AssignmentScore',
            'Create:AssignmentScore',
            'Update:AssignmentScore',
        ]);
        $data['teacher']->update(['user_id' => $guru->id]);

        $this->actingAs($guru);

        Livewire::test(InputNilaiRombel::class)
            ->set('assignmentId', $data['assignment']->id)
            ->set('scores.'.$data['studentA']->id, 80)
            ->set('scores.'.$data['studentB']->id, 90)
            ->set('notes.'.$data['studentA']->id, 'Bagus')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assignment_scores', [
            'assignment_id' => $data['assignment']->id,
            'student_id' => $data['studentA']->id,
            'score' => 80.00,
            'notes' => 'Bagus',
        ]);
        $this->assertDatabaseHas('assignment_scores', [
            'assignment_id' => $data['assignment']->id,
            'student_id' => $data['studentB']->id,
            'score' => 90.00,
        ]);
    }

    public function test_guru_tidak_bisa_menyimpan_nilai_tugas_guru_lain(): void
    {
        $data = $this->makeContext();
        $guruLain = $this->makeUserWithRole('guru', [
            'View:InputNilaiRombel',
            'Create:AssignmentScore',
            'Update:AssignmentScore',
        ]);

        $this->actingAs($guruLain);

        Livewire::test(InputNilaiRombel::class)
            ->set('assignmentId', $data['assignment']->id)
            ->set('scores.'.$data['studentA']->id, 70)
            ->call('save');

        $this->assertDatabaseMissing('assignment_scores', [
            'assignment_id' => $data['assignment']->id,
            'student_id' => $data['studentA']->id,
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
        $teachingAssignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'semester_id' => $semester->id,
        ]);
        $assignment = Assignment::create([
            'teaching_assignment_id' => $teachingAssignment->id,
            'title' => 'Tugas 1',
            'max_score' => 100,
        ]);

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

        return compact('teacher', 'assignment', 'studentA', 'studentB');
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
