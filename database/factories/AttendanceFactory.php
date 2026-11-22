<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
use App\Models\ClassEnrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $session = AttendanceSession::factory()->create();
        $student = Student::factory()->create();
        $assignment = $session->schedule->teachingAssignment;

        ClassEnrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'school_class_id' => $assignment->school_class_id,
            ],
            [
                'academic_year_id' => $assignment->schoolClass->academic_year_id,
                'status' => 'active',
                'enrolled_at' => $session->date,
            ]
        );

        $status = AttendanceStatus::firstOrCreate(
            ['code' => 'HDR'],
            [
                'name' => 'Hadir',
                'numeric_value' => 100,
                'is_active' => true,
            ]
        );

        return [
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'attendance_status_id' => $status->id,
            'notes' => null,
        ];
    }
}
