<?php

namespace App\Filament\Resources\AssignmentScores;

use App\Filament\Resources\AssignmentScores\Pages\CreateAssignmentScore;
use App\Filament\Resources\AssignmentScores\Pages\EditAssignmentScore;
use App\Filament\Resources\AssignmentScores\Pages\ListAssignmentScores;
use App\Filament\Resources\AssignmentScores\Schemas\AssignmentScoreForm;
use App\Filament\Resources\AssignmentScores\Tables\AssignmentScoresTable;
use App\Models\AssignmentScore;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AssignmentScoreResource extends Resource
{
    protected static ?string $model = AssignmentScore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Nilai Tugas';

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return AssignmentScoreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentScoresTable::configure($table);
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
            'index' => ListAssignmentScores::route('/'),
            'create' => CreateAssignmentScore::route('/create'),
            'edit' => EditAssignmentScore::route('/{record}/edit'),
        ];
    }
}
