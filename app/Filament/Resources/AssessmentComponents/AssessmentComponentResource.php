<?php

namespace App\Filament\Resources\AssessmentComponents;

use App\Filament\Resources\AssessmentComponents\Pages\CreateAssessmentComponent;
use App\Filament\Resources\AssessmentComponents\Pages\EditAssessmentComponent;
use App\Filament\Resources\AssessmentComponents\Pages\ListAssessmentComponents;
use App\Filament\Resources\AssessmentComponents\Schemas\AssessmentComponentForm;
use App\Filament\Resources\AssessmentComponents\Tables\AssessmentComponentsTable;
use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\AssessmentComponent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AssessmentComponentResource extends Resource
{
    protected static ?string $model = AssessmentComponent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $navigationLabel = 'Komponen Penilaian';

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 13;

    public static function getEloquentQuery(): Builder
    {
        return OwnTeachingDataScope::apply(parent::getEloquentQuery(), 'teachingAssignment');
    }

    public static function form(Schema $schema): Schema
    {
        return AssessmentComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentComponentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessmentComponents::route('/'),
            'create' => CreateAssessmentComponent::route('/create'),
            'edit' => EditAssessmentComponent::route('/{record}/edit'),
        ];
    }
}
