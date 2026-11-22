<?php

namespace Tests\Feature;

use App\Filament\Imports\SubjectImporter;
use App\Filament\Resources\Subjects\Pages\ListSubjects;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubjectImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_importer_creates_subject_with_grade_and_major(): void
    {
        GradeLevel::create(['name' => 'XI']);
        Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer dan Telekomunikasi']);

        SubjectImporter::test(columnMap: [
            'code' => 'code',
            'name' => 'name',
            'grade_level' => 'grade_level',
            'major' => 'major',
        ])->import([
            'code' => 'MKK-TJKT-XI',
            'name' => 'Mata Kuliah Kejuruan TJKT',
            'grade_level' => 'XI',
            'major' => 'TJKT',
        ])->assertImported();

        $subject = Subject::where('code', 'MKK-TJKT-XI')->firstOrFail();

        $this->assertSame('XI', $subject->gradeLevel?->name);
        $this->assertSame('TJKT', $subject->major?->code);
    }

    public function test_subject_importer_updates_existing_by_code_and_clears_optional_relations(): void
    {
        $grade = GradeLevel::create(['name' => 'X']);
        $major = Major::create(['code' => 'DKV', 'name' => 'Desain Komunikasi Visual']);

        Subject::create([
            'code' => 'INF',
            'name' => 'Informatika Lama',
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
        ]);

        SubjectImporter::test(columnMap: [
            'code' => 'code',
            'name' => 'name',
            'grade_level' => 'grade_level',
            'major' => 'major',
        ])->import([
            'code' => 'INF',
            'name' => 'Informatika',
            'grade_level' => '',
            'major' => '',
        ])->assertImported();

        $subject = Subject::where('code', 'INF')->firstOrFail();

        $this->assertSame(1, Subject::count());
        $this->assertSame('Informatika', $subject->name);
        $this->assertNull($subject->grade_level_id);
        $this->assertNull($subject->major_id);
    }

    public function test_subject_importer_fails_when_grade_or_major_not_found(): void
    {
        SubjectImporter::test(columnMap: [
            'code' => 'code',
            'name' => 'name',
            'grade_level' => 'grade_level',
            'major' => 'major',
        ])->import([
            'code' => 'X1',
            'name' => 'Mapel X',
            'grade_level' => 'XIII',
            'major' => 'UNKNOWN',
        ])->assertHasErrors(['grade_level', 'major']);

        $this->assertSame(0, Subject::count());
    }

    public function test_list_subjects_page_has_import_action_for_authorized_user(): void
    {
        $user = $this->makeUserWithPermissions(['ViewAny:Subject', 'Create:Subject']);

        $this->actingAs($user);

        Livewire::test(ListSubjects::class)
            ->assertActionExists('import');
    }

    public function test_list_subjects_page_hides_import_action_for_unauthorized_user(): void
    {
        $user = $this->makeUserWithPermissions(['ViewAny:Subject']);

        $this->actingAs($user);

        Livewire::test(ListSubjects::class)
            ->assertActionHidden('import');
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function makeUserWithPermissions(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        foreach ($permissions as $permission) {
            $role->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        }

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
