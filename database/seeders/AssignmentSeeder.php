<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Seed satu tugas contoh untuk setiap penugasan mengajar.
     */
    public function run(): void
    {
        foreach (TeachingAssignment::with(['semester', 'subject'])->get() as $teachingAssignment) {
            $semester = $teachingAssignment->semester;

            if (! $semester) {
                continue;
            }

            $title = 'Tugas 1 — '.$teachingAssignment->subject?->code;

            Assignment::firstOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'title' => $title,
                ],
                [
                    'description' => 'Tugas contoh untuk semester '.$semester->name.'.',
                    'due_date' => Carbon::parse($semester->end_date)->subDays(7)->toDateString(),
                    'max_score' => 100,
                ]
            );
        }
    }
}
