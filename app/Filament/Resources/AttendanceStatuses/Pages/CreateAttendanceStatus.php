<?php

namespace App\Filament\Resources\AttendanceStatuses\Pages;

use App\Filament\Resources\AttendanceStatuses\AttendanceStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceStatus extends CreateRecord
{
    protected static string $resource = AttendanceStatusResource::class;
}
