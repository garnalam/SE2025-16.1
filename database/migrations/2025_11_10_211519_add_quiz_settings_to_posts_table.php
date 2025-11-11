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
        Schema::table('posts', function (Blueprint $table) {
        // Chế độ: 'manual' (thủ công) hoặc 'random' (ngẫu nhiên)
        $table->string('quiz_mode')->default('manual')->after('shuffle_questions');

        // Điểm cho mỗi câu (nếu là đề ngẫu nhiên)
        $table->decimal('points_per_question', 5, 2)->default(1.00)->after('quiz_mode');

        // Lưu cài đặt ngẫu nhiên đã dùng: { subject_id, tag_ids, count }
        $table->json('random_quiz_settings')->nullable()->after('points_per_question');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            //
        });
    }
};
