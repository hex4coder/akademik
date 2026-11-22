<?php

namespace Tests\Feature;

use App\Filament\Resources\AssessmentComponents\AssessmentComponentResource;
use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Assignment;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssessmentComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_komponen_penilaian_valid_tersimpan(): void
    {
        $data = $this->makeContext();

        $component = AssessmentComponent::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'name' => 'Tugas Harian',
            'weight' => 40,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertTrue($component->teachingAssignment->is($data['teachingAssignment']));
        $this->assertSame(1, $data['teachingAssignment']->assessmentComponents()->count());
    }

    public function test_total_bobot_aktif_tidak_boleh_melebihi_100(): void
    {
        $data = $this->makeContext();

        AssessmentComponent::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'name' => 'Tugas Harian',
            'weight' => 70,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('tidak boleh lebih dari 100%');

        AssessmentComponent::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'name' => 'Ulangan Harian',
            'weight' => 40,
            'is_active' => true,
        ]);
    }

    public function test_tugas_harus_memakai_komponen_dari_penugasan_yang_sama(): void
    {
        $data = $this->makeContext();

        $otherTeachingAssignment = TeachingAssignment::create([
            'teacher_id' => $data['teacher']->id,
            'subject_id' => $data['subject']->id,
            'school_class_id' => $data['classB']->id,
            'semester_id' => $data['semester']->id,
        ]);

        $component = AssessmentComponent::create([
            'teaching_assignment_id' => $otherTeachingAssignment->id,
            'name' => 'Ulangan',
            'weight' => 50,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('penugasan mengajar yang sama');

        Assignment::create([
            'teaching_assignment_id' => $data['teachingAssignment']->id,
            'assessment_component_id' => $component->id,
            'title' => 'Tugas salah komponen',
            'max_score' => 100,
        ]);
    }

    public function test_guru_dapat_membuka_halaman_komponen_penilaian_miliknya(): void
    {
        $data = $this->makeContext();
        $guru = $this->makeUserWithRole('guru', [
            'ViewAny:AssessmentComponent',
            'Create:AssessmentComponent',
        ]);
        $data['teacher']->update(['user_id' => $guru->id]);

        $this->actingAs($guru)
            ->get(AssessmentComponentResource::getUrl('create'))
            ->assertOk();
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
        $classB = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 2,
            'name' => 'X TJKT 2',
        ]);
        $teacher = Teacher::create(['name' => 'Guru A', 'nip' => '111111111111111111']);
        $subject = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);

        $teachingAssignment = TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'semester_id' => $semester->id,
        ]);

        Student::create([
            'nis' => '20260001',
            'name' => 'Siswa Satu',
            'status' => 'active',
        ]);

        return compact('teachingAssignment', 'teacher', 'subject', 'semester', 'classB');
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
