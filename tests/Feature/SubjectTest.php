<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Subject;
use Database\Seeders\SubjectSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_kode_mata_pelajaran_harus_unik(): void
    {
        Subject::factory()->create(['code' => 'MTK']);

        $this->expectException(QueryException::class);

        Subject::factory()->create(['code' => 'MTK']);
    }

    public function test_mata_pelajaran_bisa_dibatasi_ke_tingkat_dan_jurusan_tertentu(): void
    {
        $gradeLevel = GradeLevel::create(['name' => 'XI']);
        $major = Major::create([
            'code' => 'TJKT',
            'name' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        ]);

        $subject = Subject::factory()
            ->forGrade($gradeLevel)
            ->forMajor($major)
            ->create();

        $this->assertTrue($subject->gradeLevel->is($gradeLevel));
        $this->assertTrue($subject->major->is($major));
        $this->assertTrue($gradeLevel->subjects->contains($subject));
        $this->assertTrue($major->subjects->contains($subject));
    }

    public function test_seeder_mengisi_mapel_umum_khusus_tingkat_dan_khusus_jurusan(): void
    {
        $gradeLevel = GradeLevel::create(['name' => 'X']);
        $major = Major::create([
            'code' => 'DKV',
            'name' => 'Desain Komunikasi Visual',
        ]);

        $this->seed(SubjectSeeder::class);
        $this->seed(SubjectSeeder::class);

        $this->assertSame(10, Subject::count());
        $this->assertNull(Subject::where('code', 'MTK')->first()->grade_level_id);
        $this->assertSame($gradeLevel->id, Subject::where('code', 'INF')->first()->grade_level_id);
        $this->assertSame($major->id, Subject::where('code', 'DKV01')->first()->major_id);
    }
}
