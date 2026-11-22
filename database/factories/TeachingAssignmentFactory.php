<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingAssignment>
 */
class TeachingAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'is_active' => true,
            ]
        );

        $semester = Semester::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'Ganjil'],
            [
                'start_date' => '2026-07-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
            ]
        );

        $gradeLevel = GradeLevel::firstOrCreate(['name' => 'X']);
        $major = Major::firstOrCreate(
            ['code' => 'TJKT'],
            ['name' => 'Teknik Jaringan Komputer dan Telekomunikasi']
        );

        $schoolClass = SchoolClass::firstOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'grade_level_id' => $gradeLevel->id,
                'major_id' => $major->id,
                'group_number' => 1,
            ],
            ['name' => 'X TJKT 1']
        );

        $teacher = Teacher::firstOrCreate(
            ['nip' => fake()->unique()->numerify('##################')],
            ['name' => fake()->name()]
        );

        return [
            'teacher_id' => $teacher->id,
            'subject_id' => Subject::factory(),
            'school_class_id' => $schoolClass->id,
            'semester_id' => $semester->id,
        ];
    }
}
