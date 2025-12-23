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
        Schema::table('study_documents', function (Blueprint $table) {
            // Thêm cột loại file (sau cột file_path cho đẹp)
            if (!Schema::hasColumn('study_documents', 'file_type')) {
                $table->string('file_type')->default('pdf')->after('file_path');
            }
            
            if (!Schema::hasColumn('study_documents', 'is_teacher_resource')) {
                $table->boolean('is_teacher_resource')->default(false)->after('file_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_documents', function (Blueprint $table) {
            // Xóa cột nếu rollback
            $table->dropColumn(['file_type', 'is_teacher_resource']);
        });
    }
};