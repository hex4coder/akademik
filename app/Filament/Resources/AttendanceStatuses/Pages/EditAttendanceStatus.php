<?php

namespace App\Filament\Resources\AttendanceStatuses\Pages;

use App\Filament\Resources\AttendanceStatuses\AttendanceStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceStatus extends EditRecord
{
    protected static string $resource = AttendanceStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
