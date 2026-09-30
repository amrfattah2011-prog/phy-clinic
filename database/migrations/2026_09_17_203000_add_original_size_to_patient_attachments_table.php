<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('original_size')->nullable()->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('patient_attachments', function (Blueprint $table) {
            $table->dropColumn('original_size');
        });
    }
};