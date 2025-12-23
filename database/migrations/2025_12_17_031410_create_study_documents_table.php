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
    Schema::create('study_documents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        // Lưu ý: Code cũ bạn dùng 'team_id' thay vì 'class_id'. 
        // Chúng ta sẽ giữ nguyên 'team_id' để tránh lỗi logic cũ, 
        // nhưng hiểu ngầm đây là ID của lớp học.
        $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete(); 

        $table->string('title');
        $table->string('file_path');
        
        // --- GIỮ LẠI CỘT CŨ (Cho AI) ---
        $table->longText('extracted_content')->nullable(); 

        // --- THÊM CỘT MỚI (Cho tính năng Góc học tập) ---
        $table->string('file_type')->default('pdf'); // Để biết icon hiển thị (pdf/img/doc)
        $table->boolean('is_teacher_resource')->default(false); // Đánh dấu tài liệu của giáo viên
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_documents');
    }
};
