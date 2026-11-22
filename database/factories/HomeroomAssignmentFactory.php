<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\HomeroomAssignment;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomeroomAssignment>
 */
class HomeroomAssignmentFactory extends Factory
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

        $gradeLevel = GradeLevel::firstOrCreate(['name' => 'X']);
        $major = Major::firstOrCreate(
            ['code' => 'TJKT'],
            ['name' => 'Teknik Jaringan Komputer dan Telekomunikasi']
        );

        $groupNumber = fake()->unique()->numberBetween(1, 200);
        $schoolClass = SchoolClass::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'major_id' => $major->id,
            'group_number' => $groupNumber,
            'name' => "X TJKT {$groupNumber}",
        ]);

        $teacher = Teacher::firstOrCreate(
            ['nip' => fake()->unique()->numerify('##################')],
            ['name' => fake()->name()]
        );

        return [
            'teacher_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
        ];
    }
}
