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
    Schema::create('attendance_sessions', function (Blueprint $table) {
        $table->id();
        
        // Liên kết với lớp học (Team)
        $table->foreignId('team_id')->constrained()->cascadeOnDelete();
        
        // Liên kết với người tạo phiên (Giáo viên)
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        // Trạng thái phiên: true (đang mở), false (đã đóng)
        $table->boolean('is_active')->default(true);
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
