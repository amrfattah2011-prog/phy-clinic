<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('therapy_plan_id')->nullable()->constrained('therapy_plans')->nullOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->string('receipt_number')->unique(); // رقم إيصال التحصيل
            $table->decimal('amount', 10, 2); // المبلغ المدفوع
            $table->date('payment_date'); // تاريخ السداد
            $table->string('payment_method')->default('cash'); // طريقة الدفع (كاش، فودافون كاش، فيزا، تحويل بنكي)
            $table->text('notes')->nullable(); // ملاحظات الدفعة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
