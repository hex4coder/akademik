<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Seed kehadiran siswa yang terdaftar aktif pada setiap pertemuan.
     */
    public function run(): void
    {
        $statuses = AttendanceStatus::whereIn('code', ['HDR', 'SKT'])->pluck('id', 'code');

        if (! $statuses->has('HDR')) {
            return;
        }

        foreach (AttendanceSession::with('schedule.teachingAssignment')->get() as $session) {
            $schoolClassId = $session->schedule?->teachingAssignment?->school_class_id;

            if (! $schoolClassId) {
                continue;
            }

            $students = Student::whereHas('enrollments', fn ($query) => $query
                ->where('school_class_id', $schoolClassId)
                ->where('status', 'active'))
                ->orderBy('name')
                ->get();

            foreach ($students as $index => $student) {
                $statusId = $statuses->has('SKT') && $index === 0
                    ? $statuses['SKT']
                    : $statuses['HDR'];

                Attendance::firstOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                    ],
                    ['attendance_status_id' => $statusId]
                );
            }
        }
    }
}
