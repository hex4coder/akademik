<?php

namespace App\Filament\Resources\AssignmentScores\Pages;

use App\Filament\Resources\AssignmentScores\AssignmentScoreResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssignmentScore extends EditRecord
{
    protected static string $resource = AssignmentScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
