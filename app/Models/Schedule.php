<?php

namespace App\Models;

use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    /** @var list<string> */
    public const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Schedule $schedule): void {
            if (! in_array($schedule->day_of_week, self::DAYS, true)) {
                throw ValidationException::withMessages([
                    'day_of_week' => 'Hari tidak valid.',
                ]);
            }

            if ($schedule->end_time <= $schedule->start_time) {
                throw ValidationException::withMessages([
                    'end_time' => 'Waktu selesai harus setelah waktu mulai.',
                ]);
            }

            $teachingAssignment = $schedule->teachingAssignment;

            if (! $teachingAssignment) {
                return;
            }

            $classId = $teachingAssignment->school_class_id;
            $conflict = static::query()
                ->whereHas('teachingAssignment', fn ($query) => $query
                    ->where('school_class_id', $classId))
                ->where('day_of_week', $schedule->day_of_week)
                ->where('id', '!=', $schedule->getKey())
                ->where('start_time', '<', $schedule->end_time)
                ->where('end_time', '>', $schedule->start_time)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'start_time' => 'Jadwal kelas ini bertabrakan dengan jadwal lain pada hari yang sama.',
                ]);
            }
        });
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }
}
