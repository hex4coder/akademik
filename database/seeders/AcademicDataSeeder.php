<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AcademicDataSeeder extends Seeder
{
    /**
     * Seed contoh kelas, siswa, dan riwayat penempatan kelas.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2026/2027')->firstOrFail();
        $gradeIds = GradeLevel::whereIn('name', ['X', 'XI', 'XII'])->pluck('id', 'name');
        $majorIds = Major::whereIn('code', ['DKV', 'TJKT', 'MPLB'])->pluck('id', 'code');

        $classBlueprints = [
            ['X', 'TJKT', 1],
            ['XI', 'TJKT', 1],
            ['XII', 'TJKT', 1],
            ['XI', 'DKV', 1],
            ['XI', 'MPLB', 1],
        ];

        $classes = [];
        foreach ($classBlueprints as [$gradeName, $majorCode, $groupNumber]) {
            $classes[] = SchoolClass::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'grade_level_id' => $gradeIds[$gradeName],
                    'major_id' => $majorIds[$majorCode],
                    'group_number' => $groupNumber,
                ],
                ['name' => "{$gradeName} {$majorCode} {$groupNumber}"]
            );
        }

        $students = [];
        foreach (range(1, 6) as $number) {
            $students[] = Student::firstOrCreate(
                ['nis' => sprintf('2026%04d', $number)],
                [
                    'name' => "Siswa Contoh {$number}",
                    'gender' => $number % 2 === 0 ? 'P' : 'L',
                    'status' => 'active',
                ]
            );
        }

        foreach ($students as $index => $student) {
            $schoolClass = $classes[$index % count($classes)];

            ClassEnrollment::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'school_class_id' => $schoolClass->id,
                ],
                [
                    'academic_year_id' => $academicYear->id,
                    'status' => 'active',
                    'enrolled_at' => $academicYear->start_date,
                    'notes' => 'Data contoh',
                ]
            );
        }
    }
}
