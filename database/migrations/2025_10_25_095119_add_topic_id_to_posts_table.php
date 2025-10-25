<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Thêm cột topic_id
            // nullable() là tạm thời để các post cũ không bị lỗi
            // sau này khi code hoàn chỉnh, bạn có thể bỏ nullable() nếu muốn
            $table->foreignId('topic_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Dòng này để xóa cột nếu bạn migrate:rollback
            $table->dropForeign(['topic_id']);
            $table->dropColumn('topic_id');
        });
    }
};