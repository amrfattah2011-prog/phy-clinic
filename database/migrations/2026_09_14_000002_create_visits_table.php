<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->date('visit_date');
            $table->text('complaint'); // الشكوى
            $table->text('diagnosis'); // التشخيص
            $table->text('doctor_notes')->nullable(); // ملاحظات الطبيب
            $table->decimal('cost', 10, 2)->default(0.00); // رسوم الكشف
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
