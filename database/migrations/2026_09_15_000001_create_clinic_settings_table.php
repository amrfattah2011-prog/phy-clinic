<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_name')->default('عيادة د. أحمد عادل');
            $table->string('doctor_name')->default('د. أحمد عادل');
            $table->string('doctor_title')->default('استشاري العلاج الطبيعي وتأهيل الإصابات وتنسيق القوام');
            $table->string('phone_1')->default('01000000000');
            $table->string('phone_2')->nullable()->default('01200000000');
            $table->string('address')->default('شارع الجمهورية - برج الأطباء - الدور الثالث');
            $table->string('license_number')->default('4892 / ع.ط');
            $table->text('receipt_footer')->nullable();
            $table->string('currency')->default('ج.م');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
    }
};
