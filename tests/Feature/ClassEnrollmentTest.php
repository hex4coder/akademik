<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_hanya_boleh_memiliki_satu_penempatan_aktif_per_tahun_ajaran(): void
    {
        [$year, $firstClass, $secondClass, $student] = $this->makeData();

        ClassEnrollment::create([
            'student_id' => $student->id,
            'school_class_id' => $firstClass->id,
            'academic_year_id' => $year->id,
            'status' => 'active',
            'enrolled_at' => $year->start_date,
        ]);

        $this->expectException(QueryException::class);

        ClassEnrollment::create([
            'student_id' => $student->id,
            'school_class_id' => $secondClass->id,
            'academic_year_id' => $year->id,
            'status' => 'active',
            'enrolled_at' => $year->start_date,
        ]);
    }

    public function test_riwayat_non_aktif_tidak_dibatasi_jumlahnya(): void
    {
        [$year, $firstClass, $secondClass, $student] = $this->makeData();

        ClassEnrollment::create([
            'student_id' => $student->id,
            'school_class_id' => $firstClass->id,
            'academic_year_id' => $year->id,
            'status' => 'active',
            'enrolled_at' => $year->start_date,
        ]);

        foreach ([$firstClass, $secondClass] as $schoolClass) {
            ClassEnrollment::create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'academic_year_id' => $year->id,
                'status' => 'cancelled',
                'enrolled_at' => $year->start_date,
            ]);
        }

        $this->assertSame(3, ClassEnrollment::count());
        $this->assertSame(1, ClassEnrollment::active()->count());
    }

    /**
     * @return array{AcademicYear, SchoolClass, SchoolClass, Student}
     */
    private function makeData(): array
    {
        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-13',
            'end_date' => '2027-06-12',
        ]);

        $grade = GradeLevel::create(['name' => 'X']);
        $major = Major::create(['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer dan Telekomunikasi']);

        $firstClass = SchoolClass::create([
            'academic_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 1,
            'name' => 'X TJKT 1',
        ]);

        $secondClass = SchoolClass::create([
            'academic_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'major_id' => $major->id,
            'group_number' => 2,
            'name' => 'X TJKT 2',
        ]);

        $student = Student::create([
            'nis' => '20260001',
            'name' => 'Siswa Uji',
            'status' => 'active',
        ]);

        return [$year, $firstClass, $secondClass, $student];
    }
}
