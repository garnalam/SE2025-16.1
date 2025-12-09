<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_gamification_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thêm cột XP và Level vào users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('xp')->default(0)->after('role'); // Điểm kinh nghiệm
            $table->unsignedInteger('level')->default(1)->after('xp');   // Cấp độ hiện tại
        });

        // 2. Bảng danh sách các Huy hiệu (Badges)
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Tên: "Ong chăm chỉ"
            $table->string('description');  // Mô tả: "Nộp 10 bài tập đúng hạn"
            $table->string('icon_path')->nullable(); // Đường dẫn ảnh huy hiệu
            $table->timestamps();
        });

        // 3. Bảng trung gian User sở hữu Badge nào (Quan hệ N-N)
        Schema::create('badge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at')->useCurrent(); // Ngày nhận
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_user');
        Schema::dropIfExists('badges');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'level']);
        });
    }
};