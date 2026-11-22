<?php

namespace Tests\Feature;

use App\Filament\Widgets\AkademikOverviewStats;
use App\Filament\Widgets\AttendanceTrendChart;
use App\Filament\Widgets\RecentAttendancesTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleBadgeWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_admin_user(): void
    {
        $admin = $this->makeUserWithRole('admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_dashboard_overview_widget_renders_for_admin(): void
    {
        $admin = $this->makeUserWithRole('admin');

        $this->actingAs($admin);

        Livewire::test(AkademikOverviewStats::class)
            ->assertSee('Siswa Aktif')
            ->assertSee('Guru')
            ->assertSee('Kelas');
    }

    public function test_attendance_trend_chart_renders(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $this->actingAs($admin);

        Livewire::test(AttendanceTrendChart::class)->assertOk();
    }

    public function test_recent_attendances_table_renders(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $this->actingAs($admin);

        Livewire::test(RecentAttendancesTable::class)
            ->assertSee('Absensi Terbaru');
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
