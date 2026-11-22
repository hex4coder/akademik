<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\Attendance;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentAttendancesTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Absensi Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('attendanceSession.date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable(),
                TextColumn::make('attendanceSession.schedule.teachingAssignment.schoolClass.name')
                    ->label('Kelas'),
                TextColumn::make('attendanceSession.schedule.teachingAssignment.subject.code')
                    ->label('Mapel'),
                TextColumn::make('attendanceStatus.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Attendance $record): string => match (true) {
                        (int) ($record->attendanceStatus?->numeric_value ?? 0) >= 75 => 'success',
                        (int) ($record->attendanceStatus?->numeric_value ?? 0) > 0 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('attendanceStatus.numeric_value')
                    ->label('Poin')
                    ->numeric(),
            ])
            ->defaultSort('attendanceSession.date', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }

    protected function getQuery(): Builder
    {
        $user = auth()->user();
        $query = Attendance::query()
            ->with(['student', 'attendanceStatus', 'attendanceSession.schedule.teachingAssignment.schoolClass', 'attendanceSession.schedule.teachingAssignment.subject'])
            ->latest('id');

        if ($user instanceof User && $user->hasRole('guru') && ! $user->hasAnyRole(['admin', 'staf', 'kepala sekolah'])) {
            $query->whereHas('attendanceSession.schedule.teachingAssignment', fn (Builder $q) => OwnTeachingDataScope::applyToTeacherId($q));
        }

        return $query;
    }
}
