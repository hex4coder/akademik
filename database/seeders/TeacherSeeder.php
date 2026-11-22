<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TeacherSeeder extends Seeder
{
    /**
     * Seed akun login dan profil guru beserta perannya.
     */
    public function run(): void
    {
        $guruRole = Role::firstOrCreate([
            'name' => 'guru',
            'guard_name' => 'web',
        ]);

        $teachers = [
            [
                'name' => 'ARDAN, S.Kom',
                'nip' => '199603142022211001',
                'email' => 'ardan43@guru.smk.belajar.id',
            ],
            [
                'name' => 'UNI ANATASYA, S.Kom',
                'nip' => '199304202024212048',
                'email' => 'unianatasya04@guru.smk.belajar.id',
            ],
        ];

        foreach ($teachers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => '123',
                ]
            );

            $user->assignRole($guruRole);

            Teacher::firstOrCreate(
                ['nip' => $data['nip']],
                [
                    'name' => $data['name'],
                    'user_id' => $user->id,
                ]
            )->update([
                'name' => $data['name'],
                'user_id' => $user->id,
            ]);
        }
    }
}
