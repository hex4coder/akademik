<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleBadgeWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_admin_badge_for_admin_user(): void
    {
        $admin = $this->makeUserWithRole('admin');

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Peran saat ini');
        $response->assertSee('Admin');
    }

    public function test_dashboard_renders_guru_badge_for_guru_user(): void
    {
        $guru = $this->makeUserWithRole('guru');

        $response = $this->actingAs($guru)->get('/admin');

        $response->assertOk();
        $response->assertSee('Guru');
    }

    private function makeUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create();

        $user->assignRole($role);

        return $user;
    }
}
