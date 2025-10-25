<?php
// ..._add_comment_toggle_to_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Thêm cột này, mặc định là true (cho phép bình luận)
            $table->boolean('are_comments_enabled')->default(true)->after('post_type');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('are_comments_enabled');
        });
    }
};