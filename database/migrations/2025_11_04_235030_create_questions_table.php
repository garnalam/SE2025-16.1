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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại tới giáo viên sở hữu câu hỏi này
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->string('type')->default('single_choice'); // 'multiple_choice', 'fill_in_blank', etc.
            // Thêm các trường khác nếu cần:
            // $table->text('explanation')->nullable(); // Giải thích đáp án
            // $table->integer('difficulty_level')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
