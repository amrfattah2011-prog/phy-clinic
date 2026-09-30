<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('medical_history')->constrained('users')->nullOnDelete();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('cost')->constrained('users')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('notes')->constrained('users')->nullOnDelete();
        });

        Schema::table('therapy_sessions', function (Blueprint $table) {
            $table->foreignId('attended_by_user_id')->nullable()->after('attended_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('weight_loss_sessions', function (Blueprint $table) {
            $table->foreignId('completed_by_user_id')->nullable()->after('is_completed')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('weight_loss_sessions', function (Blueprint $table) {
            $table->dropForeign(['completed_by_user_id']);
            $table->dropColumn('completed_by_user_id');
        });

        Schema::table('therapy_sessions', function (Blueprint $table) {
            $table->dropForeign(['attended_by_user_id']);
            $table->dropColumn('attended_by_user_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn('created_by_user_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn('created_by_user_id');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn('created_by_user_id');
        });
    }
};
