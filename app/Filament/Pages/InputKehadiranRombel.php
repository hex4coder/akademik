<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
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

class InputKehadiranRombel extends Page
{
    use HasPageShield;

    protected static ?string $title = 'Input Kehadiran Rombel';

    protected static ?string $navigationLabel = 'Input Kehadiran Rombel';

    protected static string|UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?int $navigationSort = 13;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected string $view = 'filament.pages.input-kehadiran-rombel';

    public ?int $attendanceSessionId = null;

    /** @var array<int, int|string|null> */
    public array $statuses = [];

    /** @var array<int, string|null> */
    public array $notes = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('attendanceSessionId')
                    ->label('Sesi Absensi')
                    ->placeholder('Pilih sesi absensi')
                    ->options(fn (): Collection => OwnTeachingDataScope::apply(AttendanceSession::query(), 'schedule.teachingAssignment')
                        ->with(['schedule.teachingAssignment.schoolClass', 'schedule.teachingAssignment.subject', 'schedule.teachingAssignment.semester.academicYear'])
                        ->orderByDesc('date')
                        ->orderByDesc('id')
                        ->get()
                        ->mapWithKeys(fn (AttendanceSession $session): array => [
                            $session->id => sprintf(
                                '%s | %s | %s | %s %s',
                                $session->date?->format('d-m-Y') ?? '-',
                                $session->schedule?->teachingAssignment?->schoolClass?->name ?? '-',
                                $session->schedule?->teachingAssignment?->subject?->code ?? '-',
                                $session->schedule?->teachingAssignment?->semester?->academicYear?->name ?? '-',
                                $session->schedule?->teachingAssignment?->semester?->name ?? '-',
                            ),
                        ]))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (): mixed => $this->loadRows()),
            ]);
    }

    public function updatedAttendanceSessionId(): void
    {
        $this->loadRows();
    }

    public function loadRows(): void
    {
        $this->statuses = [];
        $this->notes = [];

        $session = $this->selectedSession();
        if (! $session) {
            return;
        }

        $students = $this->studentsForSession($session);
        $existing = Attendance::query()
            ->where('attendance_session_id', $session->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $defaultStatusId = AttendanceStatus::query()
            ->where('is_active', true)
            ->orderByDesc('numeric_value')
            ->value('id');

        foreach ($students as $student) {
            $attendance = $existing->get($student->id);
            $this->statuses[$student->id] = $attendance?->attendance_status_id ?? $defaultStatusId;
            $this->notes[$student->id] = $attendance?->notes;
        }
    }

    public function save(): void
    {
        $session = $this->selectedSession();

        if (! $session) {
            Notification::make()->danger()->title('Pilih sesi absensi terlebih dahulu.')->send();

            return;
        }

        $user = auth()->user();
        $canManage = $user?->can('Create:Attendance') || $user?->can('Update:Attendance');

        if (! $canManage) {
            Notification::make()->danger()->title('Anda tidak memiliki izin menyimpan kehadiran.')->send();

            return;
        }

        $students = $this->studentsForSession($session);
        $activeStatusIds = AttendanceStatus::query()->where('is_active', true)->pluck('id')->all();
        $errors = [];
        $saved = 0;

        foreach ($students as $student) {
            $statusId = $this->statuses[$student->id] ?? null;
            $rawNote = $this->notes[$student->id] ?? null;

            if ($statusId === '' || $statusId === null) {
                $errors["statuses.{$student->id}"] = "Status kehadiran {$student->name} wajib dipilih.";

                continue;
            }

            $statusId = (int) $statusId;
            if (! in_array($statusId, $activeStatusIds, true)) {
                $errors["statuses.{$student->id}"] = "Status kehadiran {$student->name} tidak valid.";

                continue;
            }

            try {
                Attendance::updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'attendance_status_id' => $statusId,
                        'notes' => filled($rawNote) ? (string) $rawNote : null,
                    ]
                );
                $saved++;
            } catch (ValidationException $exception) {
                $message = collect($exception->errors())->flatten()->first() ?? 'Data tidak valid.';
                $errors["statuses.{$student->id}"] = "{$student->name}: {$message}";
            }
        }

        if ($errors !== []) {
            $this->addError('save', implode(' ', array_values($errors)));
            Notification::make()->danger()->title('Sebagian kehadiran gagal disimpan.')->body(implode(' ', array_values($errors)))->send();

            return;
        }

        $this->loadRows();

        Notification::make()->success()->title("{$saved} kehadiran berhasil disimpan.")->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $session = $this->selectedSession();

        return [
            'session' => $session,
            'students' => $session ? $this->studentsForSession($session) : collect(),
            'statusOptions' => AttendanceStatus::query()
                ->where('is_active', true)
                ->orderByDesc('numeric_value')
                ->get(),
        ];
    }

    private function selectedSession(): ?AttendanceSession
    {
        if (! $this->attendanceSessionId) {
            return null;
        }

        return OwnTeachingDataScope::apply(AttendanceSession::query(), 'schedule.teachingAssignment')
            ->with(['schedule.teachingAssignment.schoolClass', 'schedule.teachingAssignment.subject', 'schedule.teachingAssignment.semester.academicYear'])
            ->find($this->attendanceSessionId);
    }

    /**
     * @return Collection<int, Student>
     */
    private function studentsForSession(AttendanceSession $session): Collection
    {
        $schoolClassId = $session->schedule?->teachingAssignment?->school_class_id;

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
