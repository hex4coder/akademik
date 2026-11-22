<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\HomeroomAssignment;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\HomeroomAssignmentSeeder;
use Database\Seeders\TeacherSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomeroomAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_penugasan_wali_kelas_tersimpan_dengan_relasinya(): void
    {
        $data = $this->makeContext();

        $assignment = HomeroomAssignment::create([
            'teacher_id' => $data['teacher']->id,
            'school_class_id' => $data['classXTjkt']->id,
        ]);

        $this->assertTrue($assignment->teacher->is($data['teacher']));
        $this->assertTrue($assignment->schoolClass->is($data['classXTjkt']));
        $this->assertTrue($data['classXTjkt']->homeroomAssignment->is($assignment));
        $this->assertTrue($data['teacher']->homeroomAssignments->contains($assignment));
    }

    public function test_satu_kelas_hanya_boleh_punya_satu_wali_kelas(): void
    {
        $data = $this->makeContext();

        $attributes = [
            'teacher_id' => $data['teacher']->id,
            'school_class_id' => $data['classXTjkt']->id,
        ];

        HomeroomAssignment::create($attributes);

        $this->expectException(QueryException::class);

        HomeroomAssignment::create($attributes);
    }

    public function test_guru_boleh_menjadi_wali_kelas_di_beberapa_kelas(): void
    {
        $data = $this->makeContext();

        foreach ([$data['classXTjkt'], $data['classXITjkt']] as $schoolClass) {
            HomeroomAssignment::create([
                'teacher_id' => $data['teacher']->id,
                'school_class_id' => $schoolClass->id,
            ]);
        }

        $this->assertSame(2, $data['teacher']->homeroomAssignments()->count());
    }

    public function test_seeder_menugaskan_wali_kelas_secara_idempoten(): void
    {
        $this->makeContext();
        $this->seed(TeacherSeeder::class);
        $this->seed(HomeroomAssignmentSeeder::class);
        $this->seed(HomeroomAssignmentSeeder::class);

        $ardan = Teacher::where('nip', '199603142022211001')->firstOrFail();
        $uni = Teacher::where('nip', '199304202024212048')->firstOrFail();
        $firstClass = SchoolClass::where('name', 'X TJKT 1')->firstOrFail();
        $secondClass = SchoolClass::where('name', 'XI TJKT 1')->firstOrFail();

        $this->assertSame(2, HomeroomAssignment::count());
        $this->assertDatabaseHas('homeroom_assignments', [
            'teacher_id' => $uni->id,
            'school_class_id' => $firstClass->id,
        ]);
        $this->assertDatabaseHas('homeroom_assignments', [
            'teacher_id' => $ardan->id,
            'school_class_id' => $secondClass->id,
        ]);
    }

    public function test_halaman_buat_wali_kelas_dapat_dibuka_oleh_admin(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        foreach (['ViewAny:HomeroomAssignment', 'Create:HomeroomAssignment'] as $permissionName) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->get('/admin/homeroom-assignments/create')->assertOk();
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

        $classXTjkt = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeX->id,
            'major_id' => $majorTjkt->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);
        $classXITjkt = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeXI->id,
            'major_id' => $majorTjkt->id,
            'group_number' => 1,
            'name' => 'XI TJKT 1',
        ]);

        $teacher = Teacher::create(['name' => 'Guru A', 'nip' => '111111111111111111']);

        return compact('academicYear', 'classXTjkt', 'classXITjkt', 'teacher');
    }
}
