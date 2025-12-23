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
        // 1. Đồng bộ Role từ bảng Users sang Team User (cho các dòng bị NULL)
        // Lưu ý: Cú pháp này hoạt động tốt trên MySQL/MariaDB.
        DB::statement("
            UPDATE team_user 
            JOIN users ON team_user.user_id = users.id 
            SET team_user.role = users.role 
            WHERE team_user.role IS NULL
        ");

        // 2. Đổi tên role cũ sang role mới (admin -> teacher, editor -> student)
        DB::table('team_user')
            ->where('role', 'admin')
            ->update(['role' => 'teacher']);

        DB::table('team_user')
            ->where('role', 'editor')
            ->update(['role' => 'student']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            //
        });
    }
};
