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
     * Buat peran admin/guru/staf/kepala sekolah dan berikan izin secara idempoten.
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

        $stafRole = Role::firstOrCreate([
            'name' => 'staf',
            'guard_name' => 'web',
        ]);

        $kepalaSekolahRole = Role::firstOrCreate([
            'name' => 'kepala sekolah',
            'guard_name' => 'web',
        ]);

        $permissionNames = Permission::query()->pluck('name')->all();

        $adminRole->syncPermissions($permissionNames);

        $viewPermissionNames = collect($permissionNames)
            ->filter(fn (string $name): bool => str_starts_with($name, 'View'))
            ->values()
            ->all();

        $baseViewOnlyNames = collect($viewPermissionNames)
            ->reject(fn (string $name): bool => str_ends_with($name, ':Role')
                || str_ends_with($name, ':User')
                || str_ends_with($name, ':Permission'))
            ->values();

        $guruManagePermissionNames = collect([
            'Create:Assignment',
            'Update:Assignment',
            'Delete:Assignment',
            'Create:AssignmentScore',
            'Update:AssignmentScore',
            'Delete:AssignmentScore',
        ])->filter(fn (string $name): bool => in_array($name, $permissionNames, true));

        $guruPermissionNames = $baseViewOnlyNames
            ->merge($guruManagePermissionNames)
            ->unique()
            ->values()
            ->all();

        $guruRole->syncPermissions($guruPermissionNames);
        $stafRole->syncPermissions($baseViewOnlyNames->all());
        $kepalaSekolahRole->syncPermissions($viewPermissionNames);

        $adminUser = User::where('email', 'admin@example.com')->first();

        if ($adminUser) {
            $adminUser->assignRole($adminRole);
        }
    }
}
