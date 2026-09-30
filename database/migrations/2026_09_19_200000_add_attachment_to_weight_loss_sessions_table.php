<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weight_loss_sessions', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('doctor_notes');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_type', 20)->nullable()->after('attachment_name'); // image, pdf
            $table->string('attachment_mime', 100)->nullable()->after('attachment_type');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime'); // compressed size in bytes
            $table->unsignedBigInteger('original_size')->nullable()->after('attachment_size'); // original size in bytes
            $table->decimal('compression_ratio', 5, 2)->nullable()->default(0.00)->after('original_size'); // % saved
        });
    }

    public function down(): void
    {
        Schema::table('weight_loss_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_path',
                'attachment_name',
                'attachment_type',
                'attachment_mime',
                'attachment_size',
                'original_size',
                'compression_ratio',
            ]);
        });
    }
};
