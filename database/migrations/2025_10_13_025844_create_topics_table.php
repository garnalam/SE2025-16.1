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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            
            // Cột quan trọng: Liên kết chủ đề này với lớp học nào
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            
            // Cột quan trọng: Liên kết chủ đề này với ai là người tạo (GV)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Thông tin của chủ đề
            $table->string('name');
            $table->text('description')->nullable(); // Mô tả có thể có hoặc không
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
