<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Seed status kehadiran beserta nilai numerik yang dapat diubah kemudian.
     */
    public function run(): void
    {
        $statuses = [
            ['HDR', 'Hadir', 100],
            ['SKT', 'Sakit', 75],
            ['IZN', 'Izin', 75],
            ['ALP', 'Alpa', 0],
            ['BOL', 'Bolos', -10],
        ];

        foreach ($statuses as [$code, $name, $numericValue]) {
            AttendanceStatus::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'numeric_value' => $numericValue,
                    'is_active' => true,
                ]
            );
        }
    }
}
