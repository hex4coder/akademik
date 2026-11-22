<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\TeacherSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_membuat_dua_guru_beserta_akun_dan_perannya_secara_idempoten(): void
    {
        $this->seed(TeacherSeeder::class);
        $this->seed(TeacherSeeder::class);

        $this->assertSame(2, Teacher::count());

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
            $user = User::where('email', $data['email'])->firstOrFail();
            $teacher = Teacher::where('nip', $data['nip'])->firstOrFail();

            $this->assertSame($data['name'], $user->name);
            $this->assertTrue($user->hasRole('guru'));
            $this->assertTrue(Hash::check('123', $user->password));
            $this->assertSame($data['name'], $teacher->name);
            $this->assertSame($user->id, $teacher->user_id);
            $this->assertTrue($user->teacher->is($teacher));
        }
    }
}
