<?php

namespace App\Models;

use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Assignment $assignment): void {
            if ((int) $assignment->max_score < 1) {
                throw ValidationException::withMessages([
                    'max_score' => 'Nilai maksimal harus lebih besar dari nol.',
                ]);
            }

            $semester = $assignment->teachingAssignment?->semester;

            if (! $semester || ! $assignment->due_date) {
                return;
            }

            $dueDate = $assignment->due_date->toDateString();

            if ($dueDate < $semester->start_date || $dueDate > $semester->end_date) {
                throw ValidationException::withMessages([
                    'due_date' => 'Tenggat tugas harus berada dalam rentang semester.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'max_score' => 'integer',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssignmentScore::class);
    }
}
