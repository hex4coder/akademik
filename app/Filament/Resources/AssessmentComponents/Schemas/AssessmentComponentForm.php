<?php

namespace App\Filament\Resources\AssessmentComponents\Schemas;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\TeachingAssignment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class AssessmentComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teaching_assignment_id')
                    ->label('Penugasan mengajar')
                    ->options(fn (): Collection => OwnTeachingDataScope::applyToTeacherId(TeachingAssignment::query())
                        ->with(['teacher', 'subject', 'schoolClass', 'semester.academicYear'])
                        ->get()
                        ->mapWithKeys(fn (TeachingAssignment $assignment): array => [
                            $assignment->id => sprintf(
                                '%s %s | %s | %s | %s',
                                $assignment->semester?->academicYear?->name ?? '-',
                                $assignment->semester?->name ?? '-',
                                $assignment->schoolClass?->name ?? '-',
                                $assignment->subject?->code ?? '-',
                                $assignment->teacher?->name ?? '-',
                            ),
                        ]))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nama komponen')
                    ->required()
                    ->maxLength(255),
                TextInput::make('weight')
                    ->label('Bobot (%)')
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(100)
                    ->step(0.01)
                    ->default(0)
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->integer()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
