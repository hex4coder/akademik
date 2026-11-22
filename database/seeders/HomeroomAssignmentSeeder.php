<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class HomeroomAssignmentSeeder extends Seeder
{
    /**
     * Seed contoh wali kelas untuk tahun ajaran aktif.
     */
    public function run(): void
    {
        $teachers = Teacher::whereIn('nip', [
            '199603142022211001',
            '199304202024212048',
        ])->get()->keyBy('nip');

        $academicYear = AcademicYear::where('name', '2026/2027')->first();

        if (! $academicYear) {
            return;
        }

        $firstClass = SchoolClass::where('name', 'X TJKT 1')
            ->where('academic_year_id', $academicYear->id)
            ->first();
        $secondClass = SchoolClass::where('name', 'XI TJKT 1')
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $blueprints = [
            ['199304202024212048', $firstClass],
            ['199603142022211001', $secondClass],
        ];

        foreach ($blueprints as [$teacherNip, $schoolClass]) {
            $teacher = $teachers->get($teacherNip);

            if (! $teacher || ! $schoolClass) {
                continue;
            }

            HomeroomAssignment::firstOrCreate(
                ['school_class_id' => $schoolClass->id],
                ['teacher_id' => $teacher->id]
            );
        }
    }
}
