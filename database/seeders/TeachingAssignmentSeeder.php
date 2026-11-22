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
     * Seed contoh penugasan mengajar untuk semester aktif.
     */
    public function run(): void
    {
        $teachers = Teacher::whereIn('nip', [
            '199603142022211001',
            '199304202024212048',
        ])->get()->keyBy('nip');

        $academicYear = AcademicYear::where('name', '2026/2027')->first();
        $semester = $academicYear
            ? Semester::where('academic_year_id', $academicYear->id)->where('name', 'Ganjil')->first()
            : null;

        $firstClass = SchoolClass::where('name', 'X TJKT 1')->where('academic_year_id', $academicYear?->id)->first();
        $secondClass = SchoolClass::where('name', 'XI TJKT 1')->where('academic_year_id', $academicYear?->id)->first();

        if (! $semester || ! $firstClass || ! $secondClass) {
            return;
        }

        $blueprints = [
            ['199603142022211001', 'INF', $firstClass],
            ['199603142022211001', 'MTK', $firstClass],
            ['199603142022211001', 'TJKT01', $secondClass],
            ['199304202024212048', 'TJKT01', $firstClass],
        ];

        foreach ($blueprints as [$teacherNip, $subjectCode, $schoolClass]) {
            $teacher = $teachers->get($teacherNip);
            $subject = Subject::where('code', $subjectCode)->first();

            if (! $teacher || ! $subject) {
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
