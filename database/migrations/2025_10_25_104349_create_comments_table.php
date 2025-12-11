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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            
            // Người viết bình luận
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Bài đăng được bình luận
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            // (QUAN TRỌNG) Dùng cho tính năng Trả lời (Reply)
            // Nếu là bình luận gốc, parent_id = NULL
            // Nếu là trả lời, parent_id = ID của bình luận cha
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();

            // Nội dung bình luận
            $table->text('body');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
