<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\AttendanceSession;
use App\Models\AttendanceStatus;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('attendance_session_id')
                    ->label('Pertemuan')
                    ->options(function (): Collection {
                        return AttendanceSession::query()
                            ->with('schedule.teachingAssignment.schoolClass', 'schedule.teachingAssignment.subject')
                            ->orderByDesc('date')
                            ->get()
                            ->mapWithKeys(fn (AttendanceSession $session): array => [
                                $session->id => sprintf(
                                    '%s | %s | %s | %s',
                                    $session->date->format('d M Y'),
                                    $session->schedule?->day_of_week ?? '-',
                                    $session->schedule?->teachingAssignment?->schoolClass?->name ?? '-',
                                    $session->schedule?->teachingAssignment?->subject?->code ?? '-',
                                ),
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('student_id')
                    ->label('Siswa')
                    ->options(function (Get $get): Collection {
                        $session = AttendanceSession::find($get('attendance_session_id'));

                        if (! $session) {
                            return collect();
                        }

                        $schoolClassId = $session->schedule?->teachingAssignment?->school_class_id;

                        if (! $schoolClassId) {
                            return collect();
                        }

                        return Student::whereHas('enrollments', fn ($query) => $query
                            ->where('school_class_id', $schoolClassId)
                            ->where('status', 'active'))
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Student $student): array => [
                                $student->id => "{$student->name} — {$student->nis}",
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('attendance_status_id')
                    ->label('Status kehadiran')
                    ->relationship('attendanceStatus', 'name')
                    ->getOptionLabelFromRecordUsing(fn (AttendanceStatus $record): string => "{$record->name} ({$record->numeric_value})")
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }
}
