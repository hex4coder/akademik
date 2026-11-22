<?php

namespace Database\Seeders;

use App\Models\AttendanceSession;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSessionSeeder extends Seeder
{
    /**
     * Seed satu pertemuan untuk setiap jadwal pada minggu pertama semester.
     */
    public function run(): void
    {
        $dayMap = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
        ];

        foreach (Schedule::with('teachingAssignment.semester')->get() as $schedule) {
            $semester = $schedule->teachingAssignment?->semester;

            if (! $semester || ! isset($dayMap[$schedule->day_of_week])) {
                continue;
            }

            $date = Carbon::parse($semester->start_date);
            $date->addDays(($dayMap[$schedule->day_of_week] - $date->dayOfWeekIso + 7) % 7);
            $date = $date->toDateString();

            $exists = AttendanceSession::where('schedule_id', $schedule->id)
                ->whereDate('date', $date)
                ->exists();

            if (! $exists) {
                AttendanceSession::create([
                    'schedule_id' => $schedule->id,
                    'date' => $date,
                    'topic' => 'Pertemuan 1',
                ]);
            }
        }
    }
}
