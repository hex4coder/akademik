<?php

namespace Database\Seeders;

use App\Models\AssessmentComponent;
use App\Models\TeachingAssignment;
use Illuminate\Database\Seeder;

class AssessmentComponentSeeder extends Seeder
{
    /**
     * Seed komponen penilaian contoh per penugasan mengajar.
     */
    public function run(): void
    {
        foreach (TeachingAssignment::query()->get() as $teachingAssignment) {
            AssessmentComponent::firstOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'name' => 'Tugas Harian',
                ],
                [
                    'weight' => 40,
                    'sort_order' => 1,
                    'is_active' => true,
                ]
            );

            AssessmentComponent::firstOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'name' => 'Ulangan Harian',
                ],
                [
                    'weight' => 60,
                    'sort_order' => 2,
                    'is_active' => true,
                ]
            );
        }
    }
}
