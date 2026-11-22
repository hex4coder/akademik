<?php

namespace App\Filament\Resources\HomeroomAssignments;

use App\Filament\Resources\HomeroomAssignments\Pages\CreateHomeroomAssignment;
use App\Filament\Resources\HomeroomAssignments\Pages\EditHomeroomAssignment;
use App\Filament\Resources\HomeroomAssignments\Pages\ListHomeroomAssignments;
use App\Filament\Resources\HomeroomAssignments\Schemas\HomeroomAssignmentForm;
use App\Filament\Resources\HomeroomAssignments\Tables\HomeroomAssignmentsTable;
use App\Models\HomeroomAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HomeroomAssignmentResource extends Resource
{
    protected static ?string $model = HomeroomAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Wali Kelas';

    protected static string|UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return HomeroomAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomeroomAssignmentsTable::configure($table);
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
            'index' => ListHomeroomAssignments::route('/'),
            'create' => CreateHomeroomAssignment::route('/create'),
            'edit' => EditHomeroomAssignment::route('/{record}/edit'),
        ];
    }
}
