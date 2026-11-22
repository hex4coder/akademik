<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_statuses', function (Blueprint $table) {
            // Nilai status kehadiran boleh negatif, misalnya untuk "Bolos".
            $table->integer('numeric_value')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kolom lama tidak bisa menyimpan nilai negatif, jadi dinormalkan dulu.
        DB::table('attendance_statuses')->where('numeric_value', '<', 0)->update(['numeric_value' => 0]);

        Schema::table('attendance_statuses', function (Blueprint $table) {
            $table->unsignedTinyInteger('numeric_value')->default(0)->change();
        });
    }
};
