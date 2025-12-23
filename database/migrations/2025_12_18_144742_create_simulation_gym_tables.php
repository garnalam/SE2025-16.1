<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Bảng lưu phiên luyện tập (Mỗi lần vào gym là 1 session)
        Schema::create('gym_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode'); // 'quick', 'survival', 'mistake', 'boss'
            $table->integer('total_questions')->default(0);
            $table->integer('correct_count')->default(0);
            $table->integer('xp_earned')->default(0);
            $table->integer('streak')->default(0); // Chuỗi đúng liên tiếp cao nhất
            $table->timestamp('completed_at')->nullable(); // Nếu null nghĩa là chưa xong
            $table->timestamps();
        });

        // 2. Bảng lưu chi tiết từng câu trả lời trong phiên (Log chi tiết)
        Schema::create('gym_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete(); // Giả sử bạn đã có bảng questions
            $table->string('selected_answer')->nullable();
            $table->boolean('is_correct');
            $table->integer('time_taken_seconds')->default(0); // Thời gian suy nghĩ
            $table->timestamps();
        });

        // 3. Bảng "Sổ thù vặt" (Lưu các câu làm sai để ôn lại)
        Schema::create('user_mistakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('fail_count')->default(1); // Sai bao nhiêu lần rồi
            $table->timestamp('last_reviewed_at')->nullable(); // Lần cuối ôn lại câu này
            $table->timestamps();
            
            // Một user chỉ lưu 1 bản ghi cho 1 câu hỏi sai (sai tiếp thì tăng fail_count)
            $table->unique(['user_id', 'question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_mistakes');
        Schema::dropIfExists('gym_responses');
        Schema::dropIfExists('gym_sessions');
    }
};