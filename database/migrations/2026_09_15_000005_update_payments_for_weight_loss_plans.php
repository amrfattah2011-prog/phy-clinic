<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('weight_loss_plan_id')->nullable()->after('therapy_plan_id')->constrained('weight_loss_plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['weight_loss_plan_id']);
            $table->dropColumn(['weight_loss_plan_id']);
        });
    }
};
