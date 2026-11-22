<?php

namespace App\Filament\Resources\AttendanceSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('schedule.teachingAssignment.schoolClass.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('schedule.teachingAssignment.subject.code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('schedule.teachingAssignment.teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('schedule.day_of_week')
                    ->label('Hari')
                    ->badge(),
                TextColumn::make('topic')
                    ->label('Topik')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
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
