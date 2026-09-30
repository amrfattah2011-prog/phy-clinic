<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_loss_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('package_name'); // اسم باقة التخسيس (مثال: كورس التخسيس ونحت القوام 8 جلسات)
            $table->unsignedInteger('total_sessions')->default(8); // إجمالي عدد الجلسات
            $table->decimal('cost', 10, 2)->default(0.00); // إجمالي تكلفة الباقة
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('weight_loss_sessions', function (Blueprint $table) {
            $table->foreignId('weight_loss_plan_id')->nullable()->after('patient_id')->constrained('weight_loss_plans')->cascadeOnDelete();
            $table->unsignedInteger('session_number')->nullable()->after('weight_loss_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('weight_loss_sessions', function (Blueprint $table) {
            $table->dropForeign(['weight_loss_plan_id']);
            $table->dropColumn(['weight_loss_plan_id', 'session_number']);
        });

        Schema::dropIfExists('weight_loss_plans');
    }
};
