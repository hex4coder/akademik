<?php

namespace App\Filament\Resources\AttendanceStatuses;

use App\Filament\Resources\AttendanceStatuses\Pages\CreateAttendanceStatus;
use App\Filament\Resources\AttendanceStatuses\Pages\EditAttendanceStatus;
use App\Filament\Resources\AttendanceStatuses\Pages\ListAttendanceStatuses;
use App\Filament\Resources\AttendanceStatuses\Schemas\AttendanceStatusForm;
use App\Filament\Resources\AttendanceStatuses\Tables\AttendanceStatusesTable;
use App\Models\AttendanceStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AttendanceStatusResource extends Resource
{
    protected static ?string $model = AttendanceStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?string $navigationLabel = 'Status Kehadiran';

    protected static string|UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return AttendanceStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceStatusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceStatuses::route('/'),
            'create' => CreateAttendanceStatus::route('/create'),
            'edit' => EditAttendanceStatus::route('/{record}/edit'),
        ];
    }
}
