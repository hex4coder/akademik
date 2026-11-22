<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\ProfileSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profil_terhubung_ke_akun_login(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();

        $this->assertTrue($profile->user->is($user));
        $this->assertTrue($user->profile->is($profile));
    }

    public function test_satu_akun_hanya_boleh_punya_satu_profil(): void
    {
        $user = User::factory()->create();

        Profile::factory()->for($user)->create();

        $this->expectException(QueryException::class);

        Profile::factory()->for($user)->create();
    }

    public function test_seeder_membuat_profil_administrator_secara_idempoten(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        $this->seed(ProfileSeeder::class);
        $this->seed(ProfileSeeder::class);

        $this->assertSame(1, Profile::count());
        $this->assertSame('Administrator', $admin->profile->name);
        $this->assertSame('Operator', $admin->profile->position);
    }
}
