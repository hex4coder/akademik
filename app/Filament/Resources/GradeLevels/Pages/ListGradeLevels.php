<?php

namespace App\Filament\Resources\GradeLevels\Pages;

use App\Filament\Imports\GradeLevelImporter;
use App\Filament\Resources\GradeLevels\GradeLevelResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListGradeLevels extends ListRecords
{
    protected static string $resource = GradeLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(GradeLevelImporter::class)
                ->label('Import Tingkat Kelas')
                ->visible(fn (): bool => auth()->user()?->can('Create:GradeLevel') ?? false),
            CreateAction::make(),
        ];
    }
}
