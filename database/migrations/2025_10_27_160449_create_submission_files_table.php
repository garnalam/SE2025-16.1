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
    Schema::create('submission_files', function (Blueprint $table) {
        $table->id();
        $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade'); // Liên kết đến bài nộp
        $table->string('file_path'); // Đường dẫn lưu file
        $table->string('original_name'); // Tên file gốc
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_files');
    }
};
