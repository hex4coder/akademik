<?php

namespace App\Models;

use Database\Factories\AssignmentScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class AssignmentScore extends Model
{
    /** @use HasFactory<AssignmentScoreFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (AssignmentScore $score): void {
            $assignment = $score->assignment;

            if (! $assignment || ! $score->student) {
                return;
            }

            $schoolClassId = $assignment->teachingAssignment?->school_class_id;

            if (! $schoolClassId) {
                return;
            }

            $isEnrolled = ClassEnrollment::where('student_id', $score->student_id)
                ->where('school_class_id', $schoolClassId)
                ->where('status', 'active')
                ->exists();

            if (! $isEnrolled) {
                throw ValidationException::withMessages([
                    'student_id' => 'Siswa tidak terdaftar aktif di kelas tugas ini.',
                ]);
            }

            if ((float) $score->score < 0 || (float) $score->score > (int) $assignment->max_score) {
                throw ValidationException::withMessages([
                    'score' => "Nilai harus antara 0 dan {$assignment->max_score}.",
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'score' => 'float',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
