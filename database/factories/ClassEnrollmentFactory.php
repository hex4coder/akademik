<?php

namespace Database\Factories;

use App\Models\ClassEnrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassEnrollment>
 */
class ClassEnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => fn () => Student::query()->inRandomOrder()->value('id'),
            'school_class_id' => fn () => SchoolClass::query()->inRandomOrder()->value('id'),
            'academic_year_id' => function (array $attributes): mixed {
                return SchoolClass::find($attributes['school_class_id'])?->academic_year_id;
            },
            'status' => 'active',
            'enrolled_at' => fn () => now()->startOfYear()->toDateString(),
        ];
    }
}
