<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\TeachingAssignment;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Seed contoh jadwal mingguan untuk penugasan mengajar yang sudah ada.
     */
    public function run(): void
    {
        $blueprints = [
            ['INF', 'X TJKT 1', 'Senin', '07:30:00', '09:00:00', 'R. 101'],
            ['MTK', 'X TJKT 1', 'Senin', '09:15:00', '10:45:00', 'R. 101'],
            ['TJKT01', 'X TJKT 1', 'Selasa', '07:30:00', '10:00:00', 'Lab. TJKT'],
            ['BIN', 'XI TJKT 1', 'Rabu', '07:30:00', '09:00:00', 'R. 201'],
            ['MKK-TJKT-XI', 'XI TJKT 1', 'Rabu', '09:15:00', '11:45:00', 'Lab. TJKT'],
        ];

        foreach ($blueprints as [$subjectCode, $className, $day, $start, $end, $room]) {
            $assignment = TeachingAssignment::query()
                ->whereHas('subject', fn ($query) => $query->where('code', $subjectCode))
                ->whereHas('schoolClass', fn ($query) => $query->where('name', $className))
                ->first();

            if (! $assignment) {
                continue;
            }

            Schedule::firstOrCreate(
                [
                    'teaching_assignment_id' => $assignment->id,
                    'day_of_week' => $day,
                    'start_time' => $start,
                ],
                [
                    'end_time' => $end,
                    'room' => $room,
                ]
            );
        }
    }
}
