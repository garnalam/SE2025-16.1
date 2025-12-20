<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    // Thêm dòng này để xóa bảng cũ nếu lỡ tạo rồi
    Schema::dropIfExists('study_notebooks');

    Schema::create('study_notebooks', function (Blueprint $table) {
        $table->id();
        
        // Đổi class_id thành team_id cho đồng bộ với hệ thống cũ của bạn
        $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
        
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->longText('content')->nullable(); 
        $table->string('type')->default('notebook'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_notebooks');
    }
};
