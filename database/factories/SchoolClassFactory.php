<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => fn () => AcademicYear::query()->inRandomOrder()->value('id'),
            'grade_level_id' => fn () => GradeLevel::query()->inRandomOrder()->value('id'),
            'major_id' => fn () => Major::query()->inRandomOrder()->value('id'),
            'group_number' => fn () => fake()->unique()->numberBetween(1, 99),
            'name' => function (array $attributes): string {
                $grade = GradeLevel::find($attributes['grade_level_id'])?->name;
                $major = Major::find($attributes['major_id'])?->code;

                return trim("{$grade} {$major} {$attributes['group_number']}");
            },
        ];
    }
}
