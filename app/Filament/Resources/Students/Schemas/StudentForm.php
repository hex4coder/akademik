<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nis')
                    ->label('NIS')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Nama siswa')
                    ->required()
                    ->maxLength(255),
                Select::make('gender')
                    ->label('Jenis kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->nullable(),
                DatePicker::make('date_of_birth')
                    ->label('Tanggal lahir')
                    ->nullable()
                    ->native(false),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->nullable(),
                Select::make('status')
                    ->label('Status siswa')
                    ->options([
                        'active' => 'Aktif',
                        'graduated' => 'Lulus',
                        'transferred' => 'Pindah',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}
