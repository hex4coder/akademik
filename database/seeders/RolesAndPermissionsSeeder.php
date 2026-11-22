<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Buat peran admin/guru dan berikan izin secara idempoten.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $guruRole = Role::firstOrCreate([
            'name' => 'guru',
            'guard_name' => 'web',
        ]);

        $permissionNames = Permission::query()->pluck('name')->all();

        $adminRole->syncPermissions($permissionNames);

        $guruPermissionNames = collect($permissionNames)
            ->filter(fn (string $name): bool => str_starts_with($name, 'View'))
            ->reject(fn (string $name): bool => str_ends_with($name, ':Role') || str_ends_with($name, ':User') || str_ends_with($name, ':Permission'))
            ->values()
            ->all();

        $guruRole->syncPermissions($guruPermissionNames);

        $adminUser = User::where('email', 'admin@example.com')->first();

        if ($adminUser) {
            $adminUser->assignRole($adminRole);
        }
    }
}
