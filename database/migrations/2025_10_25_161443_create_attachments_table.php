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
        Schema::create('attachments', function (Blueprint $table) {
    $table->id();
    // Quan hệ đa hình
    $table->morphs('attachable'); // Sẽ tạo attachable_id và attachable_type
    
    $table->string('path');         // Đường dẫn lưu file (ví dụ: 'attachments/file.pdf')
    $table->string('original_name'); // Tên file gốc
    $table->string('mime_type')->nullable();
    $table->unsignedBigInteger('size')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
