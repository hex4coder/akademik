<?php

namespace App\Filament\Imports;

use App\Models\Major;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class MajorImporter extends Importer
{
    protected static ?string $model = Major::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Kode Jurusan')
                ->example('TJKT')
                ->requiredMapping()
                ->rules(fn (?Major $record): array => [
                    'required',
                    'max:20',
                    Rule::unique(Major::class, 'code')->ignore($record),
                ]),
            ImportColumn::make('name')
                ->label('Nama Jurusan')
                ->example('Teknik Jaringan Komputer dan Telekomunikasi')
                ->requiredMapping()
                ->rules(['required', 'max:150']),
        ];
    }

    public function resolveRecord(): ?Major
    {
        $code = $this->data['code'] ?? null;

        if (blank($code)) {
            return new Major;
        }

        return Major::query()->firstOrNew([
            'code' => $code,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import jurusan selesai. '.Number::format($import->successful_rows).' baris berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diimport.';
        }

        return $body;
    }
}
