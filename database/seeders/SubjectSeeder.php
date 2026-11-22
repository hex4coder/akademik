<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Seed contoh mata pelajaran umum, khusus tingkat X, dan khusus jurusan.
     */
    public function run(): void
    {
        $gradeIds = GradeLevel::whereIn('name', ['X'])->pluck('id', 'name');
        $majorIds = Major::whereIn('code', ['DKV', 'TJKT', 'MPLB'])->pluck('id', 'code');

        $subjects = [
            ['MTK', 'Matematika', null, null],
            ['BIN', 'Bahasa Indonesia', null, null],
            ['BING', 'Bahasa Inggris', null, null],
            ['PPKN', 'Pendidikan Pancasila dan Kewarganegaraan', null, null],
            ['PJOK', 'Pendidikan Jasmani, Olahraga, dan Kesehatan', null, null],
            ['SENI', 'Seni Budaya', null, null],
            ['INF', 'Informatika', 'X', null],
            ['DKV01', 'Dasar Desain Grafis', null, 'DKV'],
            ['TJKT01', 'Dasar Teknik Komputer dan Jaringan', null, 'TJKT'],
            ['MPLB01', 'Dasar Manajemen Perkantoran', null, 'MPLB'],
        ];

        foreach ($subjects as [$code, $name, $gradeName, $majorCode]) {
            Subject::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'grade_level_id' => $gradeName ? ($gradeIds[$gradeName] ?? null) : null,
                    'major_id' => $majorCode ? ($majorIds[$majorCode] ?? null) : null,
                ]
            );
        }
    }
}
