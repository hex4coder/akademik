<?php

namespace App\Filament\Resources\SchoolProfiles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SchoolProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('npsn')
                    ->default(null),
                Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('website')
                    ->url()
                    ->default(null),
                FileUpload::make('logo_path')
                    ->image()
                    ->disk('public')
                    ->directory('school-logos')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(2048),
            ]);
    }
}
