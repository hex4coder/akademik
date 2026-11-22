<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentScore;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AssignmentScoreSeeder extends Seeder
{
    /**
     * Seed nilai tugas untuk siswa yang terdaftar aktif di kelas tugas.
     */
    public function run(): void
    {
        foreach (Assignment::with('teachingAssignment')->get() as $assignment) {
            $schoolClassId = $assignment->teachingAssignment?->school_class_id;

            if (! $schoolClassId) {
                continue;
            }

            $students = Student::whereHas('enrollments', fn ($query) => $query
                ->where('school_class_id', $schoolClassId)
                ->where('status', 'active'))
                ->orderBy('name')
                ->get();

            foreach ($students as $index => $student) {
                AssignmentScore::firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'score' => min(100, 85 - ($index % 3) * 5),
                    ]
                );
            }
        }
    }
}
