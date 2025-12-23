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
        // 1. Thêm cấu hình trọng số cho Lớp học
        Schema::table('teams', function (Blueprint $table) {
            // Lưu JSON dạng: {"attendance": 10, "regular": 40, "midterm": 20, "final": 30}
            $table->json('grade_weights')->nullable()->after('personal_team');
        });

        // 2. Thêm loại điểm cho Bài đăng (để biết đâu là Giữa kỳ, Cuối kỳ)
        Schema::table('posts', function (Blueprint $table) {
            // Values: 'regular' (thường), 'midterm' (giữa kỳ), 'final' (cuối kỳ)
            $table->string('grading_type')->default('regular')->after('post_type');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('grade_weights');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('grading_type');
        });
    }
};
