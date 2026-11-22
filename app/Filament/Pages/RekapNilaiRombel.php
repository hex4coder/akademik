<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\AssessmentComponent;
use App\Models\Assignment;
use App\Models\AssignmentScore;
use App\Models\Student;
use App\Models\TeachingAssignment;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class RekapNilaiRombel extends Page
{
    use HasPageShield;

    protected static ?string $title = 'Rekap Nilai Rombel';

    protected static ?string $navigationLabel = 'Rekap Nilai Rombel';

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 17;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.rekap-nilai-rombel';

    public ?int $teachingAssignmentId = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('teachingAssignmentId')
                    ->label('Penugasan mengajar')
                    ->placeholder('Pilih penugasan mengajar')
                    ->options(fn (): Collection => OwnTeachingDataScope::applyToTeacherId(TeachingAssignment::query())
                        ->with(['teacher', 'subject', 'schoolClass', 'semester.academicYear'])
                        ->orderByDesc('id')
                        ->get()
                        ->mapWithKeys(fn (TeachingAssignment $assignment): array => [
                            $assignment->id => sprintf(
                                '%s %s | %s | %s | %s',
                                $assignment->semester?->academicYear?->name ?? '-',
                                $assignment->semester?->name ?? '-',
                                $assignment->schoolClass?->name ?? '-',
                                $assignment->subject?->code ?? '-',
                                $assignment->teacher?->name ?? '-',
                            ),
                        ]))
                    ->searchable()
                    ->preload()
                    ->live(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('gray')
                ->action(fn () => $this->js('window.print()')),
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->disabled(fn (): bool => ! $this->selectedTeachingAssignment())
                ->action(fn (): StreamedResponse => $this->exportCsv()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $teachingAssignment = $this->selectedTeachingAssignment();

        if (! $teachingAssignment) {
            return [
                'teachingAssignment' => null,
                'components' => collect(),
                'rows' => collect(),
                'totalActiveWeight' => 0,
            ];
        }

        $components = AssessmentComponent::query()
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $students = Student::query()
            ->whereHas('enrollments', fn ($query) => $query
                ->where('school_class_id', $teachingAssignment->school_class_id)
                ->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $assignments = Assignment::query()
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->get();

        $scores = AssignmentScore::query()
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $assignmentsByComponent = $assignments->groupBy('assessment_component_id');

        $rows = $students->map(function (Student $student) use ($components, $assignmentsByComponent, $scores): array {
            $studentScores = $scores->get($student->id, collect())->keyBy('assignment_id');
            $componentResults = [];
            $finalScore = 0.0;
            $usedWeight = 0.0;

            foreach ($components as $component) {
                $componentAssignments = $assignmentsByComponent->get($component->id, collect());
                $normalizedScores = [];

                foreach ($componentAssignments as $assignment) {
                    $score = $studentScores->get($assignment->id);

                    if (! $score || (int) $assignment->max_score < 1) {
                        continue;
                    }

                    $normalizedScores[] = ((float) $score->score / (int) $assignment->max_score) * 100;
                }

                if ($normalizedScores === []) {
                    $componentResults[$component->id] = null;

                    continue;
                }

                $average = round(array_sum($normalizedScores) / count($normalizedScores), 2);
                $componentResults[$component->id] = $average;
                $finalScore += $average * ((float) $component->weight / 100);
                $usedWeight += (float) $component->weight;
            }

            return [
                'student' => $student,
                'components' => $componentResults,
                'final_score' => round($finalScore, 2),
                'used_weight' => $usedWeight,
            ];
        });

        return [
            'teachingAssignment' => $teachingAssignment,
            'components' => $components,
            'rows' => $rows,
            'totalActiveWeight' => (float) $components->sum('weight'),
        ];
    }

    private function exportCsv(): StreamedResponse
    {
        $data = $this->getViewData();
        $teachingAssignment = $data['teachingAssignment'];
        $components = $data['components'];
        $rows = $data['rows'];

        $fileName = 'rekap-nilai-'.str($teachingAssignment?->schoolClass?->name ?? 'rombel')->slug().'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($components, $rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            $headers = ['NIS', 'Siswa'];
            foreach ($components as $component) {
                $headers[] = $component->name.' ('.$component->weight.'%)';
            }
            $headers[] = 'Nilai Akhir';
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $line = [
                    $row['student']->nis,
                    $row['student']->name,
                ];
                foreach ($components as $component) {
                    $value = $row['components'][$component->id] ?? null;
                    $line[] = $value === null ? '' : number_format((float) $value, 2, '.', '');
                }
                $line[] = number_format((float) $row['final_score'], 2, '.', '');
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function selectedTeachingAssignment(): ?TeachingAssignment
    {
        if (! $this->teachingAssignmentId) {
            return null;
        }

        return OwnTeachingDataScope::applyToTeacherId(TeachingAssignment::query())
            ->with(['teacher', 'subject', 'schoolClass', 'semester.academicYear'])
            ->find($this->teachingAssignmentId);
    }
}
