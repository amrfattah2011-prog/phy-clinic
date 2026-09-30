<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', ['therapy', 'weight_loss', 'both'])->default('both');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('session_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_session_id')->nullable()->constrained('therapy_sessions')->cascadeOnDelete();
            $table->foreignId('weight_loss_session_id')->nullable()->constrained('weight_loss_sessions')->cascadeOnDelete();
            $table->foreignId('clinic_device_id')->constrained('clinic_devices')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_devices');
        Schema::dropIfExists('clinic_devices');
    }
};
