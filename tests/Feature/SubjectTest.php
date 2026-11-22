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

    public function test_seeder_mengisi_mapel_umum_dan_kejuruan_per_tingkat_serta_idempoten(): void
    {
        $gradeX = GradeLevel::create(['name' => 'X']);
        $gradeXI = GradeLevel::create(['name' => 'XI']);
        $gradeXII = GradeLevel::create(['name' => 'XII']);

        Major::create(['code' => 'DKV', 'name' => 'Desain Komunikasi Visual']);
        $majorTjkt = Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer']);
        $majorMplb = Major::create(['code' => 'MPLB', 'name' => 'Manajemen Perkantoran']);

        $this->seed(SubjectSeeder::class);
        $this->seed(SubjectSeeder::class);

        $this->assertSame(28, Subject::count());

        $this->assertNull(Subject::where('code', 'MTK')->first()->grade_level_id);
        $this->assertSame($gradeX->id, Subject::where('code', 'INF')->first()->grade_level_id);
        $this->assertSame($gradeX->id, Subject::where('code', 'DKV01')->first()->grade_level_id);

        $mkkTjktXi = Subject::where('code', 'MKK-TJKT-XI')->firstOrFail();
        $this->assertSame($gradeXI->id, $mkkTjktXi->grade_level_id);
        $this->assertSame($majorTjkt->id, $mkkTjktXi->major_id);

        $kikMplbXii = Subject::where('code', 'KIK-MPLB-XII')->firstOrFail();
        $this->assertSame($gradeXII->id, $kikMplbXii->grade_level_id);
        $this->assertSame($majorMplb->id, $kikMplbXii->major_id);
        $this->assertSame('Mapel Kreatifitas, Inovasi dan Kewirausahaan', $kikMplbXii->name);
    }
}
