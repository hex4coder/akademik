<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Nama mata pelajaran')
                    ->required()
                    ->maxLength(150),
                Select::make('grade_level_id')
                    ->label('Tingkat kelas')
                    ->relationship('gradeLevel', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Kosongkan jika berlaku untuk semua tingkat kelas.'),
                Select::make('major_id')
                    ->label('Jurusan')
                    ->relationship('major', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Kosongkan jika berlaku untuk semua jurusan.'),
            ]);
    }
}
