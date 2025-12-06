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
    Schema::table('quiz_attempts', function (Blueprint $table) {
        // Đếm số lần vi phạm
        $table->integer('violation_count')->default(0)->after('score');

        // Lưu log chi tiết (JSON)
        $table->json('proctoring_logs')->nullable()->after('violation_count');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            //
        });
    }
};
