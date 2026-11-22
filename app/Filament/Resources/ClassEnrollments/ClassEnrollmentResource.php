<?php

namespace App\Filament\Resources\ClassEnrollments;

use App\Filament\Resources\ClassEnrollments\Pages\CreateClassEnrollment;
use App\Filament\Resources\ClassEnrollments\Pages\EditClassEnrollment;
use App\Filament\Resources\ClassEnrollments\Pages\ListClassEnrollments;
use App\Filament\Resources\ClassEnrollments\Schemas\ClassEnrollmentForm;
use App\Filament\Resources\ClassEnrollments\Tables\ClassEnrollmentsTable;
use App\Models\ClassEnrollment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClassEnrollmentResource extends Resource
{
    protected static ?string $model = ClassEnrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Riwayat Kelas Siswa';

    protected static string|UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return ClassEnrollmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassEnrollmentsTable::configure($table);
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
            'index' => ListClassEnrollments::route('/'),
            'create' => CreateClassEnrollment::route('/create'),
            'edit' => EditClassEnrollment::route('/{record}/edit'),
        ];
    }
}
