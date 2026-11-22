<?php

namespace App\Filament\Resources\AttendanceStatuses\Pages;

use App\Filament\Resources\AttendanceStatuses\AttendanceStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceStatuses extends ListRecords
{
    protected static string $resource = AttendanceStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
