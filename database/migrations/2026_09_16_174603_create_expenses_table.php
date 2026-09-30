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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category')->default('other'); // بند المصروف (rent, utilities, medical_supplies, maintenance, salaries, hospitality, other)
            $table->string('title'); // عنوان / بيان المصروف
            $table->decimal('amount', 10, 2); // المبلغ المصروف
            $table->date('expense_date'); // تاريخ الصرف
            $table->string('payment_method')->default('cash'); // طريقة الصرف (كاش، فيزا، محفظة إلكترونية)
            $table->string('receipt_reference')->nullable(); // رقم فاتورة أو إيصال خارجي إن وجد
            $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete(); // الموظف الذي سجل المصروف
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
