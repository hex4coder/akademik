<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
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

class RiwayatKehadiranSiswa extends Page
{
    use HasPageShield;

    protected static ?string $title = 'Riwayat Kehadiran Siswa';

    protected static ?string $navigationLabel = 'Riwayat Kehadiran Siswa';

    protected static string|UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?int $navigationSort = 14;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected string $view = 'filament.pages.riwayat-kehadiran-siswa';

    public ?int $studentId = null;

    public ?int $semesterId = null;

    public function mount(): void
    {
        $this->form->fill([
            'semesterId' => static::defaultSemesterId(),
        ]);
    }

    public static function defaultSemesterId(): ?int
    {
        $semester = Semester::query()
            ->where('is_active', true)
            ->whereHas('academicYear', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('id')
            ->first();

        return $semester?->id;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('studentId')
                    ->label('Siswa')
                    ->placeholder('Pilih siswa')
                    ->options(fn (): Collection => Student::query()
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
                            $semester->id => "{$semester->academicYear->name} — {$semester->name}",
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
            $records = Attendance::query()
                ->with([
                    'attendanceSession.schedule.teachingAssignment.subject',
                    'attendanceSession.schedule.teachingAssignment.schoolClass.gradeLevel',
                    'attendanceStatus',
                ])
                ->where('student_id', $student->id)
                ->whereHas(
                    'attendanceSession.schedule.teachingAssignment',
                    fn ($query) => $query->where('semester_id', $semester->id)
                )
                ->get()
                ->sortByDesc(fn (Attendance $attendance): string => (string) $attendance->attendanceSession?->date?->toDateString())
                ->values();

            $recap = $this->makeRecap($records);
        }

        return [
            'student' => $student,
            'semester' => $semester,
            'records' => $records,
            'recap' => $recap,
        ];
    }

    private function exportCsv(): StreamedResponse
    {
        $data = $this->getViewData();
        $student = $data['student'];
        $records = $data['records'];
        $recap = $data['recap'];

        $fileName = 'riwayat-kehadiran-'.str($student?->nis ?? 'siswa')->slug().'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($records, $recap): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Tanggal', 'Kelas', 'Mapel', 'Status', 'Poin', 'Catatan']);

            foreach ($records as $attendance) {
                $session = $attendance->attendanceSession;
                $assignment = $session?->schedule?->teachingAssignment;
                $status = $attendance->attendanceStatus;

                fputcsv($handle, [
                    $session?->date?->format('Y-m-d') ?? '',
                    $assignment?->schoolClass?->name ?? '',
                    $assignment?->subject?->code ?? '',
                    $status?->name ?? '',
                    (int) ($status?->numeric_value ?? 0),
                    $attendance->notes ?? '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['REKAP KEHADIRAN']);
            fputcsv($handle, ['Jumlah pertemuan', $recap['jumlahPertemuan'] ?? 0]);
            fputcsv($handle, ['Total poin', $recap['totalPoin'] ?? 0]);
            fputcsv($handle, ['Rata-rata poin kehadiran', $recap['rataRata'] ?? 0]);
            fputcsv($handle, ['Persentase kehadiran', ($recap['persentaseKehadiran'] ?? 0).'%']);

            fputcsv($handle, []);
            fputcsv($handle, ['Status', 'Kode', 'Nilai', 'Jumlah', 'Persentase']);
            foreach (($recap['statuses'] ?? []) as $status) {
                fputcsv($handle, [
                    $status['name'],
                    $status['code'],
                    $status['numeric_value'],
                    $status['count'],
                    $status['persentase'].'%',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  Collection<int, Attendance>  $records
     * @return array<string, mixed>
     */
    private function makeRecap(Collection $records): array
    {
        $statuses = AttendanceStatus::query()->orderByDesc('numeric_value')->get();
        $counts = $records->countBy('attendance_status_id');
        $jumlahPertemuan = $records->count();
        $totalPoin = (int) $records->sum(fn (Attendance $attendance): int => (int) ($attendance->attendanceStatus?->numeric_value ?? 0));
        $jumlahHadir = $records->filter(fn (Attendance $attendance): bool => (int) ($attendance->attendanceStatus?->numeric_value ?? 0) > 0)->count();

        return [
            'jumlahPertemuan' => $jumlahPertemuan,
            'totalPoin' => $totalPoin,
            'rataRata' => $jumlahPertemuan > 0 ? round($totalPoin / $jumlahPertemuan, 2) : 0,
            'persentaseKehadiran' => $jumlahPertemuan > 0 ? round(($jumlahHadir / $jumlahPertemuan) * 100, 1) : 0,
            'statuses' => $statuses->map(fn (AttendanceStatus $status): array => [
                'code' => $status->code,
                'name' => $status->name,
                'numeric_value' => $status->numeric_value,
                'count' => (int) ($counts[$status->id] ?? 0),
                'persentase' => $jumlahPertemuan > 0
                    ? round(((int) ($counts[$status->id] ?? 0) / $jumlahPertemuan) * 100, 1)
                    : 0,
            ]),
        ];
    }
}
