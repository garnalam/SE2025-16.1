<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gym_sessions', function (Blueprint $table) {
            // [SỬA LẠI]: Đổi subject_id thành team_id
            // constrained() sẽ tự hiểu là liên kết với bảng 'teams'
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('gym_sessions', function (Blueprint $table) {
            // Xóa khóa ngoại và cột khi rollback
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};