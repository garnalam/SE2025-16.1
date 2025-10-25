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
            // Thêm 3 cột mới cho tính năng "Bài tập"
            $table->string('title')->nullable()->after('post_type');
            $table->timestamp('due_date')->nullable()->after('title');
            $table->unsignedInteger('max_points')->nullable()->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Định nghĩa cách rollback (xóa cột)
            $table->dropColumn(['title', 'due_date', 'max_points']);
        });
    }
};