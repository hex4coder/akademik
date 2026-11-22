<?php

namespace Database\Factories;

use App\Models\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceStatus>
 */
class AttendanceStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('???'),
            'name' => fake()->word(),
            'numeric_value' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
