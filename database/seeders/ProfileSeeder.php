<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Seed contoh profil staf untuk akun administrator.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        if (! $admin) {
            return;
        }

        Profile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'name' => 'Administrator',
                'position' => 'Operator',
            ]
        );
    }
}
