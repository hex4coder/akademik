<?php

namespace App\Filament\Imports;

use App\Models\GradeLevel;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class GradeLevelImporter extends Importer
{
    protected static ?string $model = GradeLevel::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nama Tingkat')
                ->example('X')
                ->requiredMapping()
                ->rules(fn (?GradeLevel $record): array => [
                    'required',
                    'max:10',
                    Rule::unique(GradeLevel::class, 'name')->ignore($record),
                ]),
        ];
    }

    public function resolveRecord(): ?GradeLevel
    {
        $name = $this->data['name'] ?? null;

        if (blank($name)) {
            return new GradeLevel;
        }

        return GradeLevel::query()->firstOrNew([
            'name' => $name,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import tingkat kelas selesai. '.Number::format($import->successful_rows).' baris berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diimport.';
        }

        return $body;
    }
}
