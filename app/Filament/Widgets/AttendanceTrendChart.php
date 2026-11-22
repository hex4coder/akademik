<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AttendanceTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Kehadiran 14 Hari Terakhir';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $user = auth()->user();
        $isTeacher = $user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['admin', 'staf', 'kepala sekolah']);
        $activeSemester = Semester::query()->where('is_active', true)->first();

        $days = collect(range(13, 0))->map(fn (int $i): Carbon => now()->subDays($i)->startOfDay());
        $labels = $days->map(fn (Carbon $d): string => $d->format('d M'))->all();

        $query = Attendance::query()
            ->selectRaw('DATE(attendance_sessions.date) as day, SUM(CASE WHEN attendance_statuses.numeric_value > 0 THEN 1 ELSE 0 END) as hadir, SUM(CASE WHEN attendance_statuses.numeric_value <= 0 THEN 1 ELSE 0 END) as tidak_hadir')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->join('attendance_statuses', 'attendance_statuses.id', '=', 'attendances.attendance_status_id')
            ->whereBetween('attendance_sessions.date', [$days->first()->toDateString(), $days->last()->toDateString()])
            ->groupBy('day');

        if ($activeSemester) {
            $query->whereHas('attendanceSession.schedule.teachingAssignment', fn ($q) => $q->where('semester_id', $activeSemester->id));
        }

        if ($isTeacher) {
            $query->whereHas('attendanceSession.schedule.teachingAssignment', fn ($q) => OwnTeachingDataScope::applyToTeacherId($q));
        }

        $rows = $query->get()->keyBy('day');

        $hadir = [];
        $tidakHadir = [];
        foreach ($days as $day) {
            $key = $day->toDateString();
            $row = $rows->get($key);
            $hadir[] = (int) ($row->hadir ?? 0);
            $tidakHadir[] = (int) ($row->tidak_hadir ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir/bernilai positif',
                    'data' => $hadir,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22,163,74,0.15)',
                    'fill' => true,
                ],
                [
                    'label' => 'Tidak hadir/bernilai <= 0',
                    'data' => $tidakHadir,
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220,38,38,0.10)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
