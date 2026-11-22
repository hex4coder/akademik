<?php

namespace App\Filament\Resources\AssignmentScores\Schemas;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\Assignment;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class AssignmentScoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assignment_id')
                    ->label('Tugas')
                    ->options(function (): Collection {
                        return OwnTeachingDataScope::apply(Assignment::query(), 'teachingAssignment')
                            ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject'])
                            ->orderByDesc('id')
                            ->get()
                            ->mapWithKeys(fn (Assignment $assignment): array => [
                                $assignment->id => sprintf(
                                    '%s | %s | %s',
                                    $assignment->title,
                                    $assignment->teachingAssignment?->schoolClass?->name ?? '-',
                                    $assignment->teachingAssignment?->subject?->code ?? '-',
                                ),
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('student_id')
                    ->label('Siswa')
                    ->options(function (Get $get): Collection {
                        $assignment = self::resolveAssignment($get('assignment_id'));

                        if (! $assignment) {
                            return collect();
                        }

                        $schoolClassId = $assignment->teachingAssignment?->school_class_id;

                        if (! $schoolClassId) {
                            return collect();
                        }

                        return Student::whereHas('enrollments', fn ($query) => $query
                            ->where('school_class_id', $schoolClassId)
                            ->where('status', 'active'))
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Student $student): array => [
                                $student->id => "{$student->name} — {$student->nis}",
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('score')
                    ->label('Nilai')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(function (Get $get): ?float {
                        $assignment = self::resolveAssignment($get('assignment_id'));

                        return $assignment ? (float) $assignment->max_score : null;
                    })
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }

    private static function resolveAssignment(mixed $id): ?Assignment
    {
        if (! $id) {
            return null;
        }

        return OwnTeachingDataScope::apply(Assignment::query(), 'teachingAssignment')
            ->with('teachingAssignment')
            ->find($id);
    }
}
