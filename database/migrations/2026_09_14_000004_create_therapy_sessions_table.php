<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapy_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_plan_id')->constrained('therapy_plans')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->unsignedInteger('session_number'); // رقم الجلسة
            $table->date('session_date')->nullable(); // تاريخ الجلسة
            $table->boolean('is_attended')->default(false)->index(); // تم الحضور
            $table->timestamp('attended_at')->nullable(); // وقت تسجيل الحضور
            $table->text('notes')->nullable(); // ملاحظات الجلسة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapy_sessions');
    }
};
