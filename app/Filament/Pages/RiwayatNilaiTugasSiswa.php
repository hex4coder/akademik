<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\AssignmentScore;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Student;
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

class RiwayatNilaiTugasSiswa extends Page
{
    use HasPageShield;

    protected static ?string $title = 'Riwayat Nilai Tugas Siswa';

    protected static ?string $navigationLabel = 'Riwayat Nilai Tugas Siswa';

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static ?int $navigationSort = 18;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected string $view = 'filament.pages.riwayat-nilai-tugas-siswa';

    public ?int $studentId = null;

    public ?int $semesterId = null;

    public function mount(): void
    {
        $this->form->fill([
            'semesterId' => $this->defaultSemesterId(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('studentId')
                    ->label('Siswa')
                    ->placeholder('Pilih siswa')
                    ->options(fn (): Collection => OwnTeachingDataScope::apply(
                        Student::query(),
                        'enrollments.schoolClass.teachingAssignments'
                    )
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Student $student): array => [
                            $student->id => "{$student->name} — {$student->nis}",
                        ]))
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('semesterId')
                    ->label('Semester')
                    ->placeholder('Pilih semester')
                    ->options(fn (): Collection => Semester::query()
                        ->with('academicYear')
                        ->orderByDesc('id')
                        ->get()
                        ->mapWithKeys(fn (Semester $semester): array => [
                            $semester->id => "{$semester->academicYear?->name} — {$semester->name}",
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
                ->disabled(fn (): bool => ! $this->studentId || ! $this->semesterId)
                ->action(fn (): StreamedResponse => $this->exportCsv()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $student = $this->studentId ? Student::find($this->studentId) : null;
        $semester = $this->semesterId ? Semester::with('academicYear')->find($this->semesterId) : null;

        $records = collect();
        $recap = [];

        if ($student && $semester) {
            $records = OwnTeachingDataScope::apply(
                AssignmentScore::query(),
                'assignment.teachingAssignment'
            )
                ->with([
                    'assignment.assessmentComponent',
                    'assignment.teachingAssignment.subject',
                    'assignment.teachingAssignment.schoolClass',
                ])
                ->where('student_id', $student->id)
                ->whereHas(
                    'assignment.teachingAssignment',
                    fn ($query) => $query->where('semester_id', $semester->id)
                )
                ->get()
                ->sortByDesc(fn (AssignmentScore $score): string => (string) $score->assignment?->due_date?->toDateString())
                ->values();

            $recap = $this->makeRecap($records);
        }

        return [
            'student' => $student,
            'semester' => $semester,
            'records' => $records,
            'recap' => $recap,
            'schoolProfile' => SchoolProfile::query()->first(),
        ];
    }

    private function exportCsv(): StreamedResponse
    {
        $data = $this->getViewData();
        $student = $data['student'];
        $records = $data['records'];

        $fileName = 'riwayat-nilai-'.str($student?->nis ?? 'siswa')->slug().'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Tenggat', 'Kelas', 'Mapel', 'Tugas', 'Komponen', 'Nilai', 'Maks', 'Catatan']);

            foreach ($records as $score) {
                $assignment = $score->assignment;
                $teachingAssignment = $assignment?->teachingAssignment;

                fputcsv($handle, [
                    $assignment?->due_date?->format('Y-m-d') ?? '',
                    $teachingAssignment?->schoolClass?->name ?? '',
                    $teachingAssignment?->subject?->code ?? '',
                    $assignment?->title ?? '',
                    $assignment?->assessmentComponent?->name ?? '',
                    number_format((float) $score->score, 2, '.', ''),
                    $assignment?->max_score ?? '',
                    $score->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function defaultSemesterId(): ?int
    {
        $semester = Semester::query()
            ->where('is_active', true)
            ->whereHas('academicYear', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('id')
            ->first();

        return $semester?->id;
    }

    /**
     * @param  Collection<int, AssignmentScore>  $records
     * @return array<string, mixed>
     */
    private function makeRecap(Collection $records): array
    {
        $count = $records->count();
        $normalized = $records
            ->filter(fn (AssignmentScore $score): bool => (int) ($score->assignment?->max_score ?? 0) > 0)
            ->map(fn (AssignmentScore $score): float => ((float) $score->score / (int) $score->assignment->max_score) * 100);

        return [
            'jumlahTugas' => $count,
            'rataRata' => $normalized->isNotEmpty() ? round((float) $normalized->avg(), 2) : 0,
            'tertinggi' => $normalized->isNotEmpty() ? round((float) $normalized->max(), 2) : 0,
            'terendah' => $normalized->isNotEmpty() ? round((float) $normalized->min(), 2) : 0,
        ];
    }
}
