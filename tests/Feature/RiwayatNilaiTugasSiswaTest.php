<?php

namespace Tests\Feature;

use App\Filament\Pages\RiwayatNilaiTugasSiswa;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RiwayatNilaiTugasSiswaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_riwayat_nilai_tugas_menampilkan_nilai_dan_rekap(): void
    {
        $data = $this->makeContext();
        $admin = $this->makeUserWithRole('admin', ['View:RiwayatNilaiTugasSiswa']);

        $this->actingAs($admin);

        Livewire::test(RiwayatNilaiTugasSiswa::class)
            ->set('studentId', $data['student']->id)
            ->set('semesterId', $data['semester']->id)
            ->assertSee('Tugas 1')
            ->assertSee('80.00')
            ->assertSeeHtml('data-recap="jumlah-tugas">1</div>');
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
            'due_date' => '2026-08-01',
        ]);
        $student = Student::create(['nis' => '20260001', 'name' => 'Siswa A', 'status' => 'active']);
        ClassEnrollment::create([
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
            'enrolled_at' => '2026-07-01',
        ]);
        AssignmentScore::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'score' => 80,
        ]);

        return compact('student', 'semester');
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

    public function test_export_csv_riwayat_nilai_dapat_diunduh(): void
    {
        $data = $this->makeContext();
        $admin = $this->makeUserWithRole('admin', ['View:RiwayatNilaiTugasSiswa']);

        $this->actingAs($admin);

        Livewire::test(RiwayatNilaiTugasSiswa::class)
            ->set('studentId', $data['student']->id)
            ->set('semesterId', $data['semester']->id)
            ->callAction('exportCsv')
            ->assertFileDownloaded();
    }
}
