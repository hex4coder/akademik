<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('??###')),
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'grade_level_id' => null,
            'major_id' => null,
        ];
    }

    /**
     * Mata pelajaran yang hanya berlaku untuk satu tingkat kelas.
     */
    public function forGrade(GradeLevel $gradeLevel): static
    {
        return $this->state(fn (array $attributes): array => [
            'grade_level_id' => $gradeLevel->id,
        ]);
    }

    /**
     * Mata pelajaran yang hanya berlaku untuk satu jurusan.
     */
    public function forMajor(Major $major): static
    {
        return $this->state(fn (array $attributes): array => [
            'major_id' => $major->id,
        ]);
    }
}
