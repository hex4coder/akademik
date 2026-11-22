<?php

namespace App\Filament\Resources\AssignmentScores\Pages;

use App\Filament\Resources\AssignmentScores\AssignmentScoreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssignmentScores extends ListRecords
{
    protected static string $resource = AssignmentScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
