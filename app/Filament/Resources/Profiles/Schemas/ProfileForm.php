<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama staf')
                    ->required()
                    ->maxLength(255),
                Select::make('user_id')
                    ->label('Akun login')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->unique(ignoreRecord: true),
                TextInput::make('position')
                    ->label('Jabatan')
                    ->maxLength(100)
                    ->placeholder('Contoh: Kepala Sekolah, Operator'),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(20),
            ]);
    }
}
