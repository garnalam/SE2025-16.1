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
    // Thêm dòng xóa bảng cũ
    Schema::dropIfExists('document_annotations');

    Schema::create('document_annotations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('study_document_id')->constrained('study_documents')->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->json('data'); 
        $table->integer('page_number')->default(1); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_annotations');
    }
};
