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
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();

            // Liên kết với lựa chọn (option)
            $table->foreignId('poll_option_id')->constrained()->cascadeOnDelete();

            // Liên kết với người bầu (user)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->timestamps();

            // Đảm bảo một người chỉ vote 1 lần cho 1 lựa chọn
            // (Thực tế, nên là 1 user/1 post, nhưng làm thế này trước)
            $table->unique(['poll_option_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
    }
};
