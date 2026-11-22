<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\AcademicYear;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

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
                'all' => Tab::make('Semua Siswa'),
            ];
        }

        $activeEnrollment = fn (Builder $query): Builder => $query
            ->where('status', 'active')
            ->where('academic_year_id', $activeYear->id);

        return [
            'active_year' => Tab::make("Terdaftar Tahun {$activeYear->name}")
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas('enrollments', $activeEnrollment)),
            'unplaced' => Tab::make('Belum Punya Kelas')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereDoesntHave('enrollments', $activeEnrollment)),
            'all' => Tab::make('Semua Siswa'),
        ];
    }
}
