<?php

namespace App\Models;

use Database\Factories\TeachingAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class TeachingAssignment extends Model
{
    /** @use HasFactory<TeachingAssignmentFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (TeachingAssignment $assignment): void {
            $schoolClass = $assignment->schoolClass;
            $semester = $assignment->semester;
            $subject = $assignment->subject;

            if (! $schoolClass || ! $semester || ! $subject) {
                return;
            }

            if ($semester->academic_year_id != $schoolClass->academic_year_id) {
                throw ValidationException::withMessages([
                    'semester_id' => 'Semester harus berada pada tahun ajaran yang sama dengan kelas.',
                ]);
            }

            if ($subject->grade_level_id !== null && $subject->grade_level_id != $schoolClass->grade_level_id) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Mata pelajaran ini hanya berlaku untuk tingkat kelas lain.',
                ]);
            }

            if ($subject->major_id !== null && $subject->major_id != $schoolClass->major_id) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Mata pelajaran ini hanya berlaku untuk jurusan lain.',
                ]);
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
