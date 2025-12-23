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
        Schema::table('teams', function (Blueprint $table) {
            // Trọng số điểm danh (Mặc định 1)
            $table->integer('weight_attendance')->default(1)->after('late_penalty_percent');
            // Trọng số Quiz (Mặc định 3)
            $table->integer('weight_quiz')->default(3)->after('weight_attendance');
            // Trọng số Bài tập (Mặc định 6)
            $table->integer('weight_assignment')->default(6)->after('weight_quiz');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['weight_attendance', 'weight_quiz', 'weight_assignment']);
        });
    }
};
