<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use App\Models\GradeLevel;
use App\Models\Major;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class SchoolClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year_id')
                    ->label('Tahun ajaran')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::fillName($set, $get)),
                Select::make('grade_level_id')
                    ->label('Tingkat kelas')
                    ->relationship('gradeLevel', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::fillName($set, $get)),
                Select::make('major_id')
                    ->label('Jurusan')
                    ->relationship('major', 'code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::fillName($set, $get)),
                TextInput::make('group_number')
                    ->label('Kelompok')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(99)
                    ->default(1)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::fillName($set, $get))
                    ->unique(modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                        ->where('academic_year_id', $get('academic_year_id'))
                        ->where('grade_level_id', $get('grade_level_id'))
                        ->where('major_id', $get('major_id'))),
                TextInput::make('name')
                    ->label('Nama kelas')
                    ->helperText('Diisi otomatis dari tingkat, jurusan, dan kelompok.')
                    ->readOnly()
                    ->required()
                    ->maxLength(100),
            ]);
    }

    private static function fillName(Set $set, Get $get): void
    {
        $grade = GradeLevel::find($get('grade_level_id'))?->name;
        $major = Major::find($get('major_id'))?->code;
        $group = $get('group_number') ?: 1;

        $set('name', trim("{$grade} {$major} {$group}"));
    }
}
