<?php

namespace Database\Factories;

use App\Models\AssessmentComponent;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentComponent>
 */
class AssessmentComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'name' => fake()->words(2, true),
            'weight' => 20,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
