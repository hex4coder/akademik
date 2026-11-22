<?php

namespace App\Filament\Resources\AttendanceStatuses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceStatusForm
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
                    ->label('Nama status')
                    ->required()
                    ->maxLength(100),
                TextInput::make('numeric_value')
                    ->label('Nilai numerik')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Dipakai saat status kehadiran menjadi komponen nilai.'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
