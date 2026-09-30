<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->default('general'); // xray, lab, report, photo, prescription, other
            $table->date('document_date'); // تاريخ المرفق / الفحص الطبي
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_type', 20); // image, pdf
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->default(0); // in bytes
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_id', 'document_date']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_attachments');
    }
};