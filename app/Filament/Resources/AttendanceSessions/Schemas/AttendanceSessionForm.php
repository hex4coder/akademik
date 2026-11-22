<?php

namespace App\Filament\Resources\AttendanceSessions\Schemas;

use App\Models\Schedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class AttendanceSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('schedule_id')
                    ->label('Jadwal')
                    ->options(function (): Collection {
                        return Schedule::query()
                            ->with(['teachingAssignment.teacher', 'teachingAssignment.subject', 'teachingAssignment.schoolClass'])
                            ->get()
                            ->mapWithKeys(function (Schedule $schedule): array {
                                $assignment = $schedule->teachingAssignment;

                                return [$schedule->id => sprintf(
                                    '%s %s–%s | %s | %s | %s',
                                    $schedule->day_of_week,
                                    substr($schedule->start_time, 0, 5),
                                    substr($schedule->end_time, 0, 5),
                                    $assignment?->schoolClass?->name ?? '-',
                                    $assignment?->subject?->code ?? '-',
                                    $assignment?->teacher?->name ?? '-',
                                )];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required(),
                TextInput::make('topic')
                    ->label('Topik')
                    ->maxLength(255),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }
}
