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
        Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Người đăng (giáo viên)
    $table->foreignId('team_id')->constrained()->cascadeOnDelete(); // Lớp học
    $table->foreignId('topic_id')->constrained()->cascadeOnDelete(); // Chủ đề (bạn đã có)

    // Dữ liệu chung
    $table->string('post_type'); // text, poll, material, assignment
    $table->text('content');     // Nội dung, câu hỏi poll, mô tả bài tập...

    // Dành riêng cho Assignment
    $table->string('title')->nullable();      // Tiêu đề bài tập
    $table->timestamp('due_date')->nullable(); // Ngày hết hạn
    $table->unsignedInteger('max_points')->nullable(); // Điểm tối đa

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
