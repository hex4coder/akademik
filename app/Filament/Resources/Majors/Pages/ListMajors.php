<?php

namespace App\Filament\Resources\Majors\Pages;

use App\Filament\Imports\MajorImporter;
use App\Filament\Resources\Majors\MajorResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListMajors extends ListRecords
{
    protected static string $resource = MajorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(MajorImporter::class)
                ->label('Import Jurusan')
                ->visible(fn (): bool => auth()->user()?->can('Create:Major') ?? false),
            CreateAction::make(),
        ];
    }
}
