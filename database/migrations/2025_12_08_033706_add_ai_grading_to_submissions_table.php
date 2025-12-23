<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
{
    Schema::table('submissions', function (Blueprint $table) {
        // Lưu điểm gợi ý (nullable vì chưa chắc bài nào cũng dùng AI)
        $table->decimal('ai_suggested_grade', 5, 2)->nullable()->after('feedback');

        // Lưu nhận xét gợi ý
        $table->text('ai_suggested_feedback')->nullable()->after('ai_suggested_grade');
    });
}

public function down(): void
{
    Schema::table('submissions', function (Blueprint $table) {
        $table->dropColumn(['ai_suggested_grade', 'ai_suggested_feedback']);
    });
}
};
