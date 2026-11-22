<?php

namespace App\Filament\Resources\HomeroomAssignments\Schemas;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class HomeroomAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('school_class_id')
                    ->label('Kelas')
                    ->relationship('schoolClass', 'name')
                    ->getOptionLabelFromRecordUsing(fn (SchoolClass $record): string => "{$record->name} — {$record->academicYear->name}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('teacher_id')
                    ->label('Wali kelas')
                    ->relationship('teacher', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Teacher $record): string => "{$record->name} — {$record->nip}")
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
