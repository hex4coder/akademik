<?php

namespace App\Filament\Imports;

use App\Models\GradeLevel;
use App\Models\Major;
use App\Models\Subject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class SubjectImporter extends Importer
{
    protected static ?string $model = Subject::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Kode')
                ->example('MTK')
                ->requiredMapping()
                ->rules(fn (?Subject $record): array => [
                    'required',
                    'max:20',
                    Rule::unique(Subject::class, 'code')->ignore($record),
                ]),
            ImportColumn::make('name')
                ->label('Nama Mata Pelajaran')
                ->example('Matematika')
                ->requiredMapping()
                ->rules(['required', 'max:150']),
            ImportColumn::make('grade_level')
                ->label('Tingkat Kelas')
                ->example('X')
                ->helperText('Isi nama tingkat kelas (mis. X/XI/XII). Kosongkan jika berlaku untuk semua tingkat.')
                ->fillRecordUsing(function (Subject $record, ?string $state): void {
                    if (blank($state)) {
                        $record->grade_level_id = null;

                        return;
                    }

                    $record->grade_level_id = GradeLevel::query()
                        ->where('name', trim((string) $state))
                        ->value('id');
                })
                ->rules([
                    function (string $attribute, $value, \Closure $fail): void {
                        if (blank($value)) {
                            return;
                        }

                        if (! GradeLevel::query()->where('name', trim((string) $value))->exists()) {
                            $fail("Tingkat kelas [{$value}] tidak ditemukan.");
                        }
                    },
                ]),
            ImportColumn::make('major')
                ->label('Jurusan')
                ->example('TJKT')
                ->helperText('Isi kode atau nama jurusan. Kosongkan jika berlaku untuk semua jurusan.')
                ->fillRecordUsing(function (Subject $record, ?string $state): void {
                    if (blank($state)) {
                        $record->major_id = null;

                        return;
                    }

                    $value = trim((string) $state);

                    $record->major_id = Major::query()
                        ->where('code', $value)
                        ->orWhere('name', $value)
                        ->value('id');
                })
                ->rules([
                    function (string $attribute, $value, \Closure $fail): void {
                        if (blank($value)) {
                            return;
                        }

                        $value = trim((string) $value);

                        $exists = Major::query()
                            ->where('code', $value)
                            ->orWhere('name', $value)
                            ->exists();

                        if (! $exists) {
                            $fail("Jurusan [{$value}] tidak ditemukan.");
                        }
                    },
                ]),
        ];
    }

    public function resolveRecord(): ?Subject
    {
        $code = $this->data['code'] ?? null;

        if (blank($code)) {
            return new Subject;
        }

        return Subject::query()->firstOrNew([
            'code' => $code,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import mata pelajaran selesai. '.Number::format($import->successful_rows).' baris berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diimport.';
        }

        return $body;
    }
}
