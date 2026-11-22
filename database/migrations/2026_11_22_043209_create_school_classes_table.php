<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->restrictOnDelete();
            $table->foreignId('grade_level_id')
                ->constrained('grade_levels')
                ->restrictOnDelete();
            $table->foreignId('major_id')
                ->constrained('majors')
                ->restrictOnDelete();
            $table->unsignedTinyInteger('group_number')->default(1);
            $table->string('name', 100);
            $table->timestamps();

            // Nama indeks dipendekkan agar tidak melebihi batas 64 karakter MariaDB.
            $table->unique(
                ['academic_year_id', 'grade_level_id', 'major_id', 'group_number'],
                'classes_academic_group_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
