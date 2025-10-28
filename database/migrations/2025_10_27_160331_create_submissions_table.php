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
    Schema::create('submissions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained('posts')->onDelete('cascade'); // Liên kết đến bài tập (bài post)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Liên kết đến học sinh nộp bài
        $table->text('content')->nullable(); // Nếu học sinh có thể nhập text
        $table->decimal('grade', 5, 2)->nullable(); // Điểm số, ví dụ: 10.00, 8.50
        $table->text('feedback')->nullable(); // Phản hồi của giáo viên
        $table->timestamp('submitted_at')->nullable(); // Thời gian nộp bài
        $table->timestamp('graded_at')->nullable(); // Thời gian chấm bài
        $table->timestamps();

        // Đảm bảo một sinh viên chỉ nộp 1 bài cho 1 bài tập
        $table->unique(['post_id', 'user_id']); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
