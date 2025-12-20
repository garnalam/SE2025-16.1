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
    Schema::create('flashcard_sets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('team_id')->constrained()->cascadeOnDelete(); // Gắn với lớp học
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Người tạo
        $table->string('title');
        $table->string('description')->nullable();
        $table->string('color')->default('#2dd4bf'); // Màu đại diện cho đẹp
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcard_sets');
    }
};
