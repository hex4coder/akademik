<?php

namespace Tests\Feature;

use App\Filament\Imports\GradeLevelImporter;
use App\Filament\Resources\GradeLevels\Pages\ListGradeLevels;
use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GradeLevelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_level_importer_creates_new_grade_levels(): void
    {
        $import = GradeLevelImporter::test(columnMap: ['name' => 'name']);

        $import->import(['name' => 'X'])->assertImported();
        $import->import(['name' => 'XI'])->assertImported();
        $import->import(['name' => 'XII'])->assertImported();

        $this->assertDatabaseHas('grade_levels', ['name' => 'X']);
        $this->assertDatabaseHas('grade_levels', ['name' => 'XI']);
        $this->assertDatabaseHas('grade_levels', ['name' => 'XII']);
        $this->assertSame(3, GradeLevel::count());
    }

    public function test_grade_level_importer_updates_existing_by_name(): void
    {
        GradeLevel::create(['name' => 'X']);

        GradeLevelImporter::test(columnMap: ['name' => 'name'])
            ->import(['name' => 'X'])
            ->assertImported();

        $this->assertSame(1, GradeLevel::count());
        $this->assertDatabaseHas('grade_levels', ['name' => 'X']);
    }

    public function test_list_grade_levels_page_has_import_action_for_authorized_user(): void
    {
        $user = $this->makeUserWithPermissions(['ViewAny:GradeLevel', 'Create:GradeLevel']);

        $this->actingAs($user);

        Livewire::test(ListGradeLevels::class)
            ->assertActionExists('import');
    }

    public function test_list_grade_levels_page_hides_import_action_for_unauthorized_user(): void
    {
        $user = $this->makeUserWithPermissions(['ViewAny:GradeLevel']);

        $this->actingAs($user);

        Livewire::test(ListGradeLevels::class)
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
