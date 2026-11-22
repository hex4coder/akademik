<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slots = [
            ['Senin', '07:30:00', '09:00:00'],
            ['Senin', '09:15:00', '10:45:00'],
            ['Selasa', '07:30:00', '09:00:00'],
            ['Selasa', '09:15:00', '10:45:00'],
            ['Rabu', '07:30:00', '09:00:00'],
            ['Rabu', '09:15:00', '10:45:00'],
            ['Kamis', '07:30:00', '09:00:00'],
            ['Kamis', '09:15:00', '10:45:00'],
            ['Jumat', '07:30:00', '09:00:00'],
            ['Jumat', '09:15:00', '10:45:00'],
        ];

        [$day, $start, $end] = fake()->unique()->randomElement($slots);

        return [
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'room' => 'R. 101',
        ];
    }
}
