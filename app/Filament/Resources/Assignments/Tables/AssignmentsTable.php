<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Filament\Resources\OwnTeachingDataScope;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TextColumn::make('assessmentComponent.name')
                    ->label('Komponen')
                    ->placeholder('Belum diatur')
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
            ->filters([
                SelectFilter::make('school_class_id')
                    ->label('Rombel')
                    ->relationship(
                        'teachingAssignment.schoolClass',
                        'name',
                        fn (Builder $query): Builder => OwnTeachingDataScope::apply($query, 'teachingAssignments')
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('subject_id')
                    ->label('Mapel')
                    ->relationship(
                        'teachingAssignment.subject',
                        'name',
                        fn (Builder $query): Builder => OwnTeachingDataScope::apply($query, 'teachingAssignments')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->code} — {$record->name}")
                    ->searchable()
                    ->preload(),
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
