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
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Người upload
        $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete(); // Nếu null = tài liệu cá nhân, Nếu có = tài liệu lớp
        $table->string('title');
        $table->string('file_path');
        $table->longText('extracted_content')->nullable(); // Lưu text đã parse từ PDF để gửi cho AI đỡ phải parse lại
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
