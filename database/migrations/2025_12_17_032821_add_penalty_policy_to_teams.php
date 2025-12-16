<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Lưu cấu hình phạt: {"mode": 1, "fixed_percent": 30, "daily_percent": 10}
            // mode: 1 (Không phạt), 2 (Phạt cố định), 3 (Phạt theo ngày)
            $table->json('penalty_policy')->nullable()->after('grade_weights');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('penalty_policy');
        });
    }
};