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
            // Loại hình phạt: 'none' (không phạt), 'fixed' (cố định), 'daily' (theo ngày)
            $table->string('late_policy_type')->default('none')->after('personal_team');
            
            // Phần trăm bị trừ (VD: 10 nghĩa là 10%)
            $table->integer('late_penalty_percent')->default(0)->after('late_policy_type');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['late_policy_type', 'late_penalty_percent']);
        });
    }
};
