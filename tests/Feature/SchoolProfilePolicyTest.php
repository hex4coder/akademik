<?php

namespace Tests\Feature;

use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolProfilePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_hanya_bisa_lihat_profil_sekolah(): void
    {
        $profile = $this->makeSchoolProfile();

        $guru = $this->makeUserWithRole('guru', [
            'ViewAny:SchoolProfile',
            'View:SchoolProfile',
        ]);

        $this->assertTrue($guru->can('viewAny', SchoolProfile::class));
        $this->assertTrue($guru->can('view', $profile));
        $this->assertFalse($guru->can('update', $profile));
        $this->assertFalse($guru->can('delete', $profile));

        $this->actingAs($guru)->get('/admin/school-profiles')->assertOk();
        $this->actingAs($guru)->get("/admin/school-profiles/{$profile->id}/edit")->assertForbidden();
    }

    public function test_guru_tidak_bisa_membuat_profil_sekolah(): void
    {
        $this->makeSchoolProfile();

        $guru = $this->makeUserWithRole('guru', [
            'ViewAny:SchoolProfile',
            'View:SchoolProfile',
        ]);

        $this->assertFalse($guru->can('create', SchoolProfile::class));

        $this->actingAs($guru)->get('/admin/school-profiles/create')->assertForbidden();
    }

    public function test_admin_bisa_mengubah_profil_sekolah(): void
    {
        $profile = $this->makeSchoolProfile();

        $admin = $this->makeUserWithRole('admin', [
            'ViewAny:SchoolProfile',
            'View:SchoolProfile',
            'Update:SchoolProfile',
        ]);

        $this->assertTrue($admin->can('update', $profile));

        $this->actingAs($admin)->get("/admin/school-profiles/{$profile->id}/edit")->assertOk();
    }

    private function makeSchoolProfile(): SchoolProfile
    {
        return SchoolProfile::create([
            'name' => 'SMK Belajar',
            'address' => 'Jl. Contoh No. 1',
        ]);
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
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $role->givePermissionTo($permission);
        }

        $user = User::factory()->create();

        $user->assignRole($role);

        return $user;
    }
}
