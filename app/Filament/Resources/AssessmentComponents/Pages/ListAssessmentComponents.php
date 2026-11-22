<?php

namespace App\Filament\Resources\AssessmentComponents\Pages;

use App\Filament\Resources\AssessmentComponents\AssessmentComponentResource;
use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\Subject;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssessmentComponents extends ListRecords
{
    protected static string $resource = AssessmentComponentResource::class;

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
        $subjects = OwnTeachingDataScope::apply(Subject::query(), 'teachingAssignments')
            ->whereHas('teachingAssignments.assessmentComponents')
            ->orderBy('name')
            ->get();

        $tabs = [
            'all' => Tab::make('Semua Mapel'),
        ];

        foreach ($subjects as $subject) {
            $tabs['subject_'.$subject->id] = Tab::make($subject->code ?? $subject->name)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereHas(
                        'teachingAssignment',
                        fn (Builder $query): Builder => $query->where('subject_id', $subject->id)
                    ));
        }

        return $tabs;
    }
}
