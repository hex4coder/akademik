<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Seed mata pelajaran umum dan kejuruan sesuai jenjang kelas.
     */
    public function run(): void
    {
        $gradeIds = GradeLevel::whereIn('name', ['X', 'XI', 'XII'])->pluck('id', 'name');
        $majorIds = Major::whereIn('code', ['DKV', 'TJKT', 'MPLB'])->pluck('id', 'code');

        $subjects = [
            ['MTK', 'Matematika', null, null],
            ['BIN', 'Bahasa Indonesia', null, null],
            ['BING', 'Bahasa Inggris', null, null],
            ['PPKN', 'Pendidikan Pancasila dan Kewarganegaraan', null, null],
            ['PJOK', 'Pendidikan Jasmani, Olahraga, dan Kesehatan', null, null],
            ['SENI', 'Seni Budaya', null, null],
            ['INF', 'Informatika', 'X', null],
            ['DKV01', 'Dasar Desain Grafis', 'X', 'DKV'],
            ['TJKT01', 'Dasar Teknik Komputer dan Jaringan', 'X', 'TJKT'],
            ['MPLB01', 'Dasar Manajemen Perkantoran', 'X', 'MPLB'],
        ];

        foreach ($subjects as [$code, $name, $gradeName, $majorCode]) {
            $subject = Subject::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'grade_level_id' => $gradeName ? ($gradeIds[$gradeName] ?? null) : null,
                    'major_id' => $majorCode ? ($majorIds[$majorCode] ?? null) : null,
                ]
            );

            if ($gradeName && $majorCode) {
                $subject->update(['grade_level_id' => $gradeIds[$gradeName]]);
            }
        }

        $kejuruanNames = [
            'MP' => 'Mapel Pilihan Kejuruan',
            'MKK' => 'Mapel Konsentrasi Keahlian',
            'KIK' => 'Mapel Kreatifitas, Inovasi dan Kewirausahaan',
        ];

        foreach ($majorIds as $majorCode => $majorId) {
            foreach (['XI', 'XII'] as $gradeName) {
                foreach ($kejuruanNames as $kind => $name) {
                    Subject::firstOrCreate(
                        ['code' => "{$kind}-{$majorCode}-{$gradeName}"],
                        [
                            'name' => $name,
                            'grade_level_id' => $gradeIds[$gradeName],
                            'major_id' => $majorId,
                        ]
                    );
                }
            }
        }
    }
}
