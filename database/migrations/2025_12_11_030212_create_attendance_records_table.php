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
    Schema::create('attendance_records', function (Blueprint $table) {
        $table->id();
        
        // Thuộc về phiên điểm danh nào
        $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
        
        // Ai là người điểm danh (Học sinh)
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        // Thời gian điểm danh
        $table->timestamp('joined_at')->useCurrent();
        
        // Trạng thái (có mặt, muộn...) - Mặc định là 'present'
        $table->string('status')->default('present');

        $table->timestamps();

        // Ràng buộc: Một học sinh chỉ được có 1 dòng trong 1 phiên (tránh spam)
        $table->unique(['attendance_session_id', 'user_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
