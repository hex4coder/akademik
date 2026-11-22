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
        if (! Schema::hasIndex('class_enrollments', 'enrollments_student_id_index')) {
            Schema::table('class_enrollments', function (Blueprint $table) {
                $table->index('student_id', 'enrollments_student_id_index');
            });
        }

        if (Schema::hasIndex('class_enrollments', 'enrollments_student_class_unique')) {
            Schema::table('class_enrollments', function (Blueprint $table) {
                $table->dropUnique('enrollments_student_class_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_enrollments', function (Blueprint $table) {
            $table->unique(['student_id', 'school_class_id'], 'enrollments_student_class_unique');
        });
    }
};
