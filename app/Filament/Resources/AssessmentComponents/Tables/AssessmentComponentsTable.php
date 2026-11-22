<?php

namespace App\Filament\Resources\AssessmentComponents\Tables;

use App\Filament\Resources\OwnTeachingDataScope;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssessmentComponentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Komponen')
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
                TextColumn::make('weight')
                    ->label('Bobot (%)')
                    ->numeric(2)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
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
            ->defaultSort('sort_order')
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
