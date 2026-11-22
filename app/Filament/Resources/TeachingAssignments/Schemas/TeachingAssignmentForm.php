<?php

namespace App\Filament\Resources\TeachingAssignments\Schemas;

use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class TeachingAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Teacher $record): string => "{$record->name} — {$record->nip}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('school_class_id')
                    ->label('Kelas')
                    ->relationship('schoolClass', 'name')
                    ->getOptionLabelFromRecordUsing(fn (SchoolClass $record): string => "{$record->name} — {$record->academicYear->name}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('subject_id')
                    ->label('Mata pelajaran')
                    ->options(function (Get $get): Collection {
                        $schoolClass = SchoolClass::find($get('school_class_id'));

                        if (! $schoolClass) {
                            return collect();
                        }

                        return Subject::query()
                            ->where(fn ($query) => $query
                                ->whereNull('grade_level_id')
                                ->orWhere('grade_level_id', $schoolClass->grade_level_id))
                            ->where(fn ($query) => $query
                                ->whereNull('major_id')
                                ->orWhere('major_id', $schoolClass->major_id))
                            ->orderBy('code')
                            ->get()
                            ->mapWithKeys(fn (Subject $subject): array => [
                                $subject->id => "{$subject->code} — {$subject->name}",
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('semester_id')
                    ->label('Semester')
                    ->options(function (Get $get): Collection {
                        $schoolClass = SchoolClass::find($get('school_class_id'));

                        if (! $schoolClass) {
                            return collect();
                        }

                        return Semester::query()
                            ->where('academic_year_id', $schoolClass->academic_year_id)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Semester $semester): array => [
                                $semester->id => "{$semester->name} — {$semester->academicYear->name}",
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
