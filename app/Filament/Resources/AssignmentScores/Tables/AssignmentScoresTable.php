<?php

namespace App\Filament\Resources\AssignmentScores\Tables;

use App\Filament\Resources\OwnTeachingDataScope;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentScoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assignment.title')
                    ->label('Tugas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignment.teachingAssignment.schoolClass.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignment.teachingAssignment.subject.code')
                    ->label('Mapel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.nis')
                    ->label('NIS')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('school_class_id')
                    ->label('Rombel')
                    ->relationship(
                        'assignment.teachingAssignment.schoolClass',
                        'name',
                        fn (Builder $query): Builder => OwnTeachingDataScope::apply($query, 'teachingAssignments')
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('subject_id')
                    ->label('Mapel')
                    ->relationship(
                        'assignment.teachingAssignment.subject',
                        'name',
                        fn (Builder $query): Builder => OwnTeachingDataScope::apply($query, 'teachingAssignments')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->code} — {$record->name}")
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
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
