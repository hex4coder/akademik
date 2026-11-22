<?php

namespace Tests\Feature;

use App\Filament\Imports\MajorImporter;
use App\Filament\Resources\Majors\Pages\ListMajors;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MajorImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_major_importer_creates_new_major(): void
    {
        $rows = [
            ['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer dan Telekomunikasi'],
            ['code' => 'DKV', 'name' => 'Desain Komunikasi Visual'],
        ];

        $import = MajorImporter::test(columnMap: [
            'code' => 'code',
            'name' => 'name',
        ]);

        foreach ($rows as $row) {
            $import->import($row)->assertImported();
        }

        $this->assertDatabaseHas('majors', ['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer dan Telekomunikasi']);
        $this->assertDatabaseHas('majors', ['code' => 'DKV', 'name' => 'Desain Komunikasi Visual']);
        $this->assertSame(2, Major::count());
    }

    public function test_major_importer_updates_existing_major_by_code(): void
    {
        Major::create(['code' => 'TJKT', 'name' => 'Nama Lama']);

        MajorImporter::test(columnMap: [
            'code' => 'code',
            'name' => 'name',
        ])->import([
            'code' => 'TJKT',
            'name' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ])->assertImported();

        $this->assertSame(1, Major::count());
        $this->assertDatabaseHas('majors', [
            'code' => 'TJKT',
            'name' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ]);
    }

    public function test_list_majors_page_has_import_action_for_authorized_user(): void
    {
        $user = $this->makeUserWithPermission('Create:Major');
        $user->givePermissionTo(Permission::firstOrCreate(['name' => 'ViewAny:Major', 'guard_name' => 'web']));

        $this->actingAs($user);

        Livewire::test(ListMajors::class)
            ->assertActionExists('import');
    }

    public function test_list_majors_page_hides_import_action_for_unauthorized_user(): void
    {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'ViewAny:Major', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);

        $this->actingAs($user);

        Livewire::test(ListMajors::class)
            ->assertActionHidden('import');
    }

    private function makeUserWithPermission(string $permission): User
    {
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        $role->givePermissionTo($perm);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
