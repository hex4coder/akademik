<?php

namespace App\Filament\Resources\Majors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MajorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Jurusan')
                    ->placeholder('Contoh: TJKT')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),

                TextInput::make('name')
                    ->label('Nama Jurusan')
                    ->placeholder('Contoh: Teknik Jaringan Komputer dan Telekomunikasi')
                    ->required()
                    ->maxLength(150),
            ]);
    }
}
