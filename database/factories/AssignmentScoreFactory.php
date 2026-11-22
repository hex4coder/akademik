<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\AssignmentScore;
use App\Models\ClassEnrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentScore>
 */
class AssignmentScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assignment = Assignment::factory()->create();
        $student = Student::factory()->create();
        $semester = $assignment->teachingAssignment->semester;

        ClassEnrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'school_class_id' => $assignment->teachingAssignment->school_class_id,
                'status' => 'active',
            ],
            [
                'academic_year_id' => $semester->academic_year_id,
                'enrolled_at' => $semester->start_date,
            ]
        );

        return [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'score' => fake()->numberBetween(0, (int) $assignment->max_score),
            'notes' => null,
        ];
    }
}
