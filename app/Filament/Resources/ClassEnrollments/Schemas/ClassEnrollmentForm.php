<?php

namespace App\Filament\Resources\ClassEnrollments\Schemas;

use App\Models\SchoolClass;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ClassEnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Student $record): string => "{$record->nis} — {$record->name}")
                    ->searchable(['nis', 'name'])
                    ->preload()
                    ->required(),
                Select::make('school_class_id')
                    ->label('Kelas')
                    ->relationship('schoolClass', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        $set('academic_year_id', SchoolClass::find($get('school_class_id'))?->academic_year_id);
                    }),
                Hidden::make('academic_year_id'),
                DatePicker::make('enrolled_at')
                    ->label('Tanggal masuk kelas')
                    ->native(false)
                    ->default(now()->toDateString())
                    ->required(),
                Select::make('status')
                    ->label('Status penempatan')
                    ->options([
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'transferred' => 'Pindah kelas',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('active')
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }
}
