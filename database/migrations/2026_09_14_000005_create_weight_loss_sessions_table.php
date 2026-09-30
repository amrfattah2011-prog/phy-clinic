<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_loss_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->date('session_date'); // تاريخ الجلسة
            $table->decimal('weight', 5, 2)->nullable(); // الوزن كجم
            $table->decimal('height', 5, 2)->nullable(); // الطول سم
            $table->decimal('bmi', 5, 2)->nullable(); // مؤشر كتلة الجسم BMI
            $table->string('dosage_amount')->nullable(); // كمية الجرعة (مثال: 0.6 ملغ، 1 ملغ، أمبول)
            $table->string('injection_type')->nullable(); // نوع الحقنة (مثال: ساكسندا، أوزمبيك، مونجارو، ميزوثيرابي)
            $table->text('doctor_notes')->nullable(); // ملاحظات الطبيب وتوجيهات الحمية
            $table->decimal('cost', 10, 2)->default(0.00); // تكلفة الجلسة / الجرعة
            $table->boolean('is_completed')->default(false)->index(); // تم أخذ الجرعة / تنفيذ الجلسة
            $table->timestamp('completed_at')->nullable(); // تاريخ ووقت أخذ الجرعة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_loss_sessions');
    }
};
