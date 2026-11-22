<?php

namespace App\Models;

use Database\Factories\AttendanceSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class AttendanceSession extends Model
{
    /** @use HasFactory<AttendanceSessionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (AttendanceSession $session): void {
            $semester = $session->schedule?->teachingAssignment?->semester;

            if (! $semester || ! $session->date) {
                return;
            }

            $date = $session->date->toDateString();

            if ($date < $semester->start_date || $date > $semester->end_date) {
                throw ValidationException::withMessages([
                    'date' => 'Tanggal pertemuan harus berada dalam rentang semester.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
