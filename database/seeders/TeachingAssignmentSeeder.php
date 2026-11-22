<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Seeder;

class TeachingAssignmentSeeder extends Seeder
{
    /**
     * Seed contoh penugasan mengajar guru ARDAN untuk semester aktif.
     */
    public function run(): void
    {
        $teacher = Teacher::where('nip', '199603142022211001')->first();
        $academicYear = AcademicYear::where('name', '2026/2027')->first();
        $semester = $academicYear
            ? Semester::where('academic_year_id', $academicYear->id)->where('name', 'Ganjil')->first()
            : null;

        $firstClass = SchoolClass::where('name', 'X TJKT 1')->where('academic_year_id', $academicYear?->id)->first();
        $secondClass = SchoolClass::where('name', 'XI TJKT 1')->where('academic_year_id', $academicYear?->id)->first();

        if (! $teacher || ! $semester || ! $firstClass || ! $secondClass) {
            return;
        }

        $blueprints = [
            ['INF', $firstClass],
            ['MTK', $firstClass],
            ['TJKT01', $secondClass],
        ];

        foreach ($blueprints as [$subjectCode, $schoolClass]) {
            $subject = Subject::where('code', $subjectCode)->first();

            if (! $subject) {
                continue;
            }

            TeachingAssignment::firstOrCreate([
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'school_class_id' => $schoolClass->id,
                'semester_id' => $semester->id,
            ]);
        }
    }
}
