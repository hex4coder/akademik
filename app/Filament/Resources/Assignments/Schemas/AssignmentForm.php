<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\AssessmentComponent;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teaching_assignment_id')
                    ->label('Penugasan mengajar')
                    ->options(function (): Collection {
                        return OwnTeachingDataScope::applyToTeacherId(TeachingAssignment::query())
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
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('assessment_component_id')
                    ->label('Komponen penilaian')
                    ->options(function (Get $get): Collection {
                        $teachingAssignmentId = $get('teaching_assignment_id');

                        if (! $teachingAssignmentId) {
                            return collect();
                        }

                        return AssessmentComponent::query()
                            ->where('teaching_assignment_id', $teachingAssignmentId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (AssessmentComponent $component): array => [
                                $component->id => "{$component->name} ({$component->weight}%)",
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('title')
                    ->label('Judul tugas')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),
                DatePicker::make('due_date')
                    ->label('Tenggat')
                    ->live()
                    ->minDate(fn (Get $get): ?string => self::semesterDate($get, 'start_date'))
                    ->maxDate(fn (Get $get): ?string => self::semesterDate($get, 'end_date')),
                TextInput::make('max_score')
                    ->label('Nilai maksimal')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(100)
                    ->required(),
            ]);
    }

    private static function semesterDate(Get $get, string $column): ?string
    {
        $assignment = self::resolveTeachingAssignment($get('teaching_assignment_id'));
        $date = $assignment?->semester?->{$column};

        return $date ? Carbon::parse($date)->toDateString() : null;
    }

    private static function resolveTeachingAssignment(mixed $id): ?TeachingAssignment
    {
        if (! $id) {
            return null;
        }

        return OwnTeachingDataScope::applyToTeacherId(TeachingAssignment::query())
            ->with('semester')
            ->find($id);
    }
}
