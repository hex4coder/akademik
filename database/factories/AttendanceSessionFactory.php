<?php

namespace Database\Factories;

use App\Models\AttendanceSession;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'date' => '2026-07-20',
            'topic' => fake()->sentence(3),
            'notes' => null,
        ];
    }
}
