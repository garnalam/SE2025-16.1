<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            // Kiểm tra nếu chưa có cột session_date thì thêm vào
            if (!Schema::hasColumn('attendance_sessions', 'session_date')) {
                $table->dateTime('session_date')->nullable()->after('user_id');
            }
            
            // Kiểm tra nếu chưa có cột is_open thì thêm vào
            if (!Schema::hasColumn('attendance_sessions', 'is_open')) {
                $table->boolean('is_open')->default(true)->after('session_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_sessions', 'session_date')) {
                $table->dropColumn('session_date');
            }
            if (Schema::hasColumn('attendance_sessions', 'is_open')) {
                $table->dropColumn('is_open');
            }
        });
    }
};