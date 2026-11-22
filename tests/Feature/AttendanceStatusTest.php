<?php

namespace Tests\Feature;

use App\Models\AttendanceStatus;
use App\Models\User;
use Database\Seeders\AttendanceStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_membuat_lima_status_kehadiran_secara_idempoten(): void
    {
        $this->seed(AttendanceStatusSeeder::class);
        $this->seed(AttendanceStatusSeeder::class);

        $this->assertSame(5, AttendanceStatus::count());
        $this->assertSame(100, AttendanceStatus::where('code', 'HDR')->firstOrFail()->numeric_value);
        $this->assertSame(75, AttendanceStatus::where('code', 'SKT')->firstOrFail()->numeric_value);
        $this->assertSame(75, AttendanceStatus::where('code', 'IZN')->firstOrFail()->numeric_value);
        $this->assertSame(0, AttendanceStatus::where('code', 'ALP')->firstOrFail()->numeric_value);
        $this->assertSame(-10, AttendanceStatus::where('code', 'BOL')->firstOrFail()->numeric_value);
    }

    public function test_nilai_status_dapat_diubah(): void
    {
        $this->seed(AttendanceStatusSeeder::class);

        AttendanceStatus::where('code', 'SKT')->firstOrFail()->update(['numeric_value' => 80]);

        $this->assertSame(80, AttendanceStatus::where('code', 'SKT')->firstOrFail()->numeric_value);
    }

    public function test_halaman_buat_status_kehadiran_dapat_dibuka_oleh_admin(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        foreach (['ViewAny:AttendanceStatus', 'Create:AttendanceStatus'] as $permissionName) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]));
        }

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->get('/admin/attendance-statuses/create')->assertOk();
    }
}
