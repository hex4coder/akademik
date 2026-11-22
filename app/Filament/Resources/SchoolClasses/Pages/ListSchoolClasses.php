<?php

namespace App\Filament\Resources\SchoolClasses\Pages;

use App\Filament\Resources\SchoolClasses\SchoolClassResource;
use App\Models\AcademicYear;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSchoolClasses extends ListRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $activeYear = AcademicYear::active();

        if (! $activeYear) {
            return [
                'all' => Tab::make('Semua Tahun Ajaran'),
            ];
        }

        return [
            'active_year' => Tab::make("Tahun Ajaran Aktif — {$activeYear->name}")
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('academic_year_id', $activeYear->id)),
            'all' => Tab::make('Semua Tahun Ajaran'),
        ];
    }
}
