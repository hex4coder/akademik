<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\Assignment;
use App\Models\AssignmentScore;
use App\Models\Student;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class InputNilaiRombel extends Page
{
    use HasPageShield;

    protected static ?string $title = 'Input Nilai Rombel';

    protected static ?string $navigationLabel = 'Input Nilai Rombel';

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 16;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected string $view = 'filament.pages.input-nilai-rombel';

    public ?int $assignmentId = null;

    /** @var array<int, string|int|float|null> */
    public array $scores = [];

    /** @var array<int, string|null> */
    public array $notes = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assignmentId')
                    ->label('Tugas')
                    ->placeholder('Pilih tugas')
                    ->options(fn (): Collection => OwnTeachingDataScope::apply(Assignment::query(), 'teachingAssignment')
                        ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject', 'teachingAssignment.semester.academicYear'])
                        ->orderByDesc('id')
                        ->get()
                        ->mapWithKeys(fn (Assignment $assignment): array => [
                            $assignment->id => sprintf(
                                '%s | %s | %s | %s %s',
                                $assignment->title,
                                $assignment->teachingAssignment?->schoolClass?->name ?? '-',
                                $assignment->teachingAssignment?->subject?->code ?? '-',
                                $assignment->teachingAssignment?->semester?->academicYear?->name ?? '-',
                                $assignment->teachingAssignment?->semester?->name ?? '-',
                            ),
                        ]))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (): mixed => $this->loadRows()),
            ]);
    }

    public function updatedAssignmentId(): void
    {
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $this->scores = [];
        $this->notes = [];

        $assignment = $this->selectedAssignment();

        if (! $assignment) {
            return;
        }

        $students = $this->studentsForAssignment($assignment);
        $existing = AssignmentScore::query()
            ->where('assignment_id', $assignment->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        foreach ($students as $student) {
            $score = $existing->get($student->id);
            $this->scores[$student->id] = $score?->score;
            $this->notes[$student->id] = $score?->notes;
        }
    }

    public function save(): void
    {
        $assignment = $this->selectedAssignment();

        if (! $assignment) {
            Notification::make()
                ->danger()
                ->title('Pilih tugas terlebih dahulu.')
                ->send();

            return;
        }

        $user = auth()->user();
        if (! $user?->can('create', AssignmentScore::class) && ! $user?->can('update', $assignment->scores()->first() ?? new AssignmentScore(['assignment_id' => $assignment->id]))) {
            // fallback check by permission names
        }

        $canManage = $user?->can('Create:AssignmentScore') || $user?->can('Update:AssignmentScore');
        if (! $canManage) {
            Notification::make()
                ->danger()
                ->title('Anda tidak memiliki izin menyimpan nilai tugas.')
                ->send();

            return;
        }

        $students = $this->studentsForAssignment($assignment);
        $errors = [];
        $saved = 0;

        foreach ($students as $student) {
            $rawScore = $this->scores[$student->id] ?? null;
            $rawNote = $this->notes[$student->id] ?? null;

            if ($rawScore === '' || $rawScore === null) {
                continue;
            }

            if (! is_numeric($rawScore)) {
                $errors["scores.{$student->id}"] = "Nilai {$student->name} harus berupa angka.";

                continue;
            }

            $scoreValue = (float) $rawScore;
            if ($scoreValue < 0 || $scoreValue > (float) $assignment->max_score) {
                $errors["scores.{$student->id}"] = "Nilai {$student->name} harus antara 0 dan {$assignment->max_score}.";

                continue;
            }

            try {
                AssignmentScore::updateOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'score' => $scoreValue,
                        'notes' => filled($rawNote) ? (string) $rawNote : null,
                    ]
                );
                $saved++;
            } catch (ValidationException $exception) {
                $message = collect($exception->errors())->flatten()->first() ?? 'Nilai tidak valid.';
                $errors["scores.{$student->id}"] = "{$student->name}: {$message}";
            }
        }

        if ($errors !== []) {
            $this->addError('save', implode(' ', array_values($errors)));
            Notification::make()
                ->danger()
                ->title('Sebagian nilai gagal disimpan.')
                ->body(implode(' ', array_values($errors)))
                ->send();

            return;
        }

        $this->loadRows();

        Notification::make()
            ->success()
            ->title("{$saved} nilai berhasil disimpan.")
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $assignment = $this->selectedAssignment();

        return [
            'assignment' => $assignment,
            'students' => $assignment ? $this->studentsForAssignment($assignment) : collect(),
        ];
    }

    private function selectedAssignment(): ?Assignment
    {
        if (! $this->assignmentId) {
            return null;
        }

        return OwnTeachingDataScope::apply(Assignment::query(), 'teachingAssignment')
            ->with(['teachingAssignment.schoolClass', 'teachingAssignment.subject', 'teachingAssignment.semester.academicYear'])
            ->find($this->assignmentId);
    }

    /**
     * @return Collection<int, Student>
     */
    private function studentsForAssignment(Assignment $assignment): Collection
    {
        $schoolClassId = $assignment->teachingAssignment?->school_class_id;

        if (! $schoolClassId) {
            return collect();
        }

        return Student::query()
            ->whereHas('enrollments', fn ($query) => $query
                ->where('school_class_id', $schoolClassId)
                ->where('status', 'active'))
            ->orderBy('name')
            ->get();
    }
}
