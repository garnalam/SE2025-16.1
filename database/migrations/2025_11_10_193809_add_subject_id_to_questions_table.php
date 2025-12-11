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
        Schema::table('questions', function (Blueprint $table) {
            // Thêm sau cột 'user_id'
            $table->foreignId('subject_id')
                  ->nullable() // Cho phép câu hỏi cũ (chưa phân loại) là null
                  ->constrained() // Liên kết tới bảng 'subjects'
                  ->onDelete('set null') // Nếu xóa môn học, câu hỏi không bị xóa
                  ->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
