<?php

namespace App\Filament\Resources\Assignments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tugas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teachingAssignment.schoolClass.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teachingAssignment.subject.code')
                    ->label('Mapel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teachingAssignment.teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teachingAssignment.semester.name')
                    ->label('Semester')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Tenggat')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('max_score')
                    ->label('Nilai maksimal')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('due_date', 'desc')
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
