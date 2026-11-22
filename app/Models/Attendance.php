<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Attendance $attendance): void {
            $session = $attendance->attendanceSession;

            if (! $session || ! $attendance->student) {
                return;
            }

            $schoolClassId = $session->schedule?->teachingAssignment?->school_class_id;

            if (! $schoolClassId) {
                return;
            }

            $isEnrolled = ClassEnrollment::where('student_id', $attendance->student_id)
                ->where('school_class_id', $schoolClassId)
                ->where('status', 'active')
                ->exists();

            if (! $isEnrolled) {
                throw ValidationException::withMessages([
                    'student_id' => 'Siswa tidak terdaftar aktif di kelas pada pertemuan ini.',
                ]);
            }

            $status = $attendance->attendanceStatus;

            if ($status && ! $status->is_active) {
                throw ValidationException::withMessages([
                    'attendance_status_id' => 'Status kehadiran tidak aktif.',
                ]);
            }
        });
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class);
    }
}
