<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_membuat_role_staf_dan_kepala_sekolah_dengan_izin_lihat(): void
    {
        foreach (['View:Student', 'ViewAny:Student', 'View:User', 'ViewAny:User', 'Update:User'] as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $this->seed(RolesAndPermissionsSeeder::class);

        $staf = Role::findByName('staf');
        $kepalaSekolah = Role::findByName('kepala sekolah');

        $this->assertTrue($staf->hasPermissionTo('View:Student'));
        $this->assertFalse($staf->hasPermissionTo('View:User'));
        $this->assertTrue($kepalaSekolah->hasPermissionTo('View:User'));
        $this->assertFalse($kepalaSekolah->hasPermissionTo('Update:User'));
    }

    public function test_role_staf_dan_kepala_sekolah_bisa_mengakses_panel_admin(): void
    {
        $panel = Filament::getPanel('admin');

        foreach (['staf', 'kepala sekolah'] as $roleName) {
            $user = User::factory()->create();
            $user->assignRole(Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]));

            $this->assertTrue($user->canAccessPanel($panel));
        }
    }

    public function test_role_di_luar_daftar_tidak_bisa_mengakses_panel_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate([
            'name' => 'operator',
            'guard_name' => 'web',
        ]));

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }
}
