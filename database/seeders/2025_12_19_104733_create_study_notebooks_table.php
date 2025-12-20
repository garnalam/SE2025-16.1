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
    Schema::create('study_notebooks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('class_id')->nullable()->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->longText('content')->nullable(); // Lưu HTML/JSON từ TipTap editor
        $table->string('type')->default('notebook'); // 'notebook' hoặc 'spreadsheet'
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_notebooks');
    }
};
