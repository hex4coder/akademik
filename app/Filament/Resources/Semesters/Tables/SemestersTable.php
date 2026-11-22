<?php

namespace App\Filament\Resources\Semesters\Tables;

use App\Models\Semester;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SemestersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academicYear.name')
                    ->badge()
                    ->color(fn (Semester $semester) => $semester->academicYear->is_active ? 'success' : '')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('Aktifkan')
                    ->authorize('update')
                    ->visible(fn (Semester $Semester): bool => auth()->user()?->hasRole('admin') && ! $Semester->is_active && $Semester->academicYear->is_active)
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation()
                    ->action(function (Semester $Semester) {
                        Semester::query()->where('is_active', true)->update([
                            'is_active' => false,
                        ]);

                        $Semester->update([
                            'is_active' => true,
                        ]);
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Diaktifkan')
                            ->body('Semester berhasil diaktifkan')
                    ),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
