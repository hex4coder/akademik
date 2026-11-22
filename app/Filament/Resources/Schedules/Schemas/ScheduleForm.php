<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Schedule;
use App\Models\TeachingAssignment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teaching_assignment_id')
                    ->label('Penugasan mengajar')
                    ->options(function (): Collection {
                        return TeachingAssignment::query()
                            ->with(['teacher', 'subject', 'schoolClass'])
                            ->get()
                            ->mapWithKeys(fn (TeachingAssignment $assignment): array => [
                                $assignment->id => sprintf(
                                    '%s — %s | %s | %s',
                                    $assignment->subject?->code ?? '-',
                                    $assignment->subject?->name ?? '-',
                                    $assignment->schoolClass?->name ?? '-',
                                    $assignment->teacher?->name ?? '-',
                                ),
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('day_of_week')
                    ->label('Hari')
                    ->options(array_combine(Schedule::DAYS, Schedule::DAYS))
                    ->required(),
                TimePicker::make('start_time')
                    ->label('Waktu mulai')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('Waktu selesai')
                    ->seconds(false)
                    ->required(),
                TextInput::make('room')
                    ->label('Ruang')
                    ->maxLength(100)
                    ->placeholder('Contoh: R. 101, Lab. TJKT'),
            ]);
    }
}
