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
    Schema::table('attachments', function (Blueprint $table) {
        // Bỏ đoạn ->after('file_path') đi
        $table->longText('extracted_content')->nullable(); 
    });
}

public function down(): void
{
    Schema::table('attachments', function (Blueprint $table) {
        $table->dropColumn('extracted_content');
    });
}
};
