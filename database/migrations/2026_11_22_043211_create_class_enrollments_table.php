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
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete();
            $table->foreignId('school_class_id')
                ->constrained('school_classes')
                ->restrictOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->restrictOnDelete();
            $table->string('status', 20)->default('active');
            $table->date('enrolled_at');
            $table->text('notes')->nullable();

            // Kolom turunan ini bernilai NULL untuk status selain "active", sehingga
            // indeks unik di bawah mengizinkan banyak riwayat non-aktif, tetapi
            // hanya satu penempatan aktif per siswa per tahun ajaran.
            $table->unsignedBigInteger('active_student')
                ->storedAs("CASE WHEN status = 'active' THEN student_id END");

            $table->timestamps();

            $table->index('student_id', 'enrollments_student_id_index');
            $table->unique(['active_student', 'academic_year_id'], 'enrollments_one_active_per_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};
