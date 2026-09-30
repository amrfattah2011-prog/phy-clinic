<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapy_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('package_name'); // اسم باقة العلاج الطبيعي
            $table->unsignedInteger('total_sessions')->default(12); // إجمالي عدد الجلسات
            $table->decimal('cost', 10, 2)->default(0.00); // إجمالي تكلفة الباقة
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapy_plans');
    }
};
