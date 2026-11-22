<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AkademikOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $activeYear = AcademicYear::query()->where('is_active', true)->first();
        $activeSemester = Semester::query()
            ->where('is_active', true)
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->first();

        $isTeacher = $user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['admin', 'staf', 'kepala sekolah']);

        if ($isTeacher) {
            $teachingQuery = OwnTeachingDataScope::applyToTeacherId(TeachingAssignment::query())
                ->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id));

            $classIds = (clone $teachingQuery)->pluck('school_class_id')->filter()->unique();
            $teachingIds = (clone $teachingQuery)->pluck('id');

            $studentCount = Student::query()
                ->whereHas('enrollments', fn ($q) => $q
                    ->whereIn('school_class_id', $classIds)
                    ->where('status', 'active'))
                ->distinct('students.id')
                ->count('students.id');

            $attendanceCount = Attendance::query()
                ->whereHas('attendanceSession.schedule.teachingAssignment', fn ($q) => $q->whereIn('teaching_assignments.id', $teachingIds))
                ->count();

            $assignmentCount = Assignment::query()->whereIn('teaching_assignment_id', $teachingIds)->count();

            return [
                Stat::make('Kelas Diampu', $classIds->count())
                    ->description($activeSemester ? "{$activeSemester->academicYear?->name} {$activeSemester->name}" : 'Semester aktif belum diset')
                    ->icon('heroicon-o-academic-cap')
                    ->color('primary'),
                Stat::make('Siswa Aktif', $studentCount)
                    ->description('Siswa aktif di rombel yang diampu')
                    ->icon('heroicon-o-users')
                    ->color('success'),
                Stat::make('Penugasan Mengajar', $teachingIds->count())
                    ->description('Mapel/kelas pada semester aktif')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info'),
                Stat::make('Absensi Tercatat', $attendanceCount)
                    ->description("{$assignmentCount} tugas tercatat")
                    ->icon('heroicon-o-check-circle')
                    ->color('warning'),
            ];
        }

        $studentCount = Student::query()->where('status', 'active')->count();
        $teacherCount = Teacher::query()->count();
        $classCount = SchoolClass::query()
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();
        $teachingCount = TeachingAssignment::query()
            ->when($activeSemester, fn ($q) => $q->where('semester_id', $activeSemester->id))
            ->count();
        $sessionCount = AttendanceSession::query()
            ->when($activeSemester, fn ($q) => $q->whereHas('schedule.teachingAssignment', fn ($qq) => $qq->where('semester_id', $activeSemester->id)))
            ->count();
        $assignmentCount = Assignment::query()
            ->when($activeSemester, fn ($q) => $q->whereHas('teachingAssignment', fn ($qq) => $qq->where('semester_id', $activeSemester->id)))
            ->count();

        $period = $activeYear ? $activeYear->name : 'Tahun ajaran aktif belum diset';
        if ($activeSemester) {
            $period .= " · {$activeSemester->name}";
        }

        return [
            Stat::make('Siswa Aktif', $studentCount)
                ->description('Total siswa berstatus aktif')
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make('Guru', $teacherCount)
                ->description('Total guru terdaftar')
                ->icon('heroicon-o-user-group')
                ->color('info'),
            Stat::make('Kelas', $classCount)
                ->description($period)
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),
            Stat::make('Penugasan Mengajar', $teachingCount)
                ->description("{$sessionCount} sesi absensi · {$assignmentCount} tugas")
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning'),
        ];
    }
}
