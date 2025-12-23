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
    Schema::create('flashcards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('flashcard_set_id')->constrained()->cascadeOnDelete();
        $table->text('front_content'); // Mặt trước (Câu hỏi/Từ vựng)
        $table->text('back_content');  // Mặt sau (Đáp án/Nghĩa)
        $table->string('image_url')->nullable(); // (Optional) Ảnh minh họa
        $table->integer('status')->default(0); // 0: Mới, 1: Đang nhớ, 2: Đã thuộc
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
