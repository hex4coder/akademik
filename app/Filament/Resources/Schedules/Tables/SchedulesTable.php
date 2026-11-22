<?php

namespace App\Filament\Resources\Schedules\Tables;

use App\Models\Schedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teachingAssignment.schoolClass.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teachingAssignment.subject.code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teachingAssignment.subject.name')
                    ->label('Mata pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teachingAssignment.teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->badge()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Mulai')
                    ->formatStateUsing(fn (?string $state): string => $state ? substr($state, 0, 5) : '-')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Selesai')
                    ->formatStateUsing(fn (?string $state): string => $state ? substr($state, 0, 5) : '-')
                    ->sortable(),
                TextColumn::make('room')
                    ->label('Ruang')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Hari')
                    ->options(array_combine(Schedule::DAYS, Schedule::DAYS)),
            ])
            ->defaultSort('day_of_week')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
