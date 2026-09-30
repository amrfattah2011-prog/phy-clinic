<?php

use App\Models\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('portal_token', 64)->nullable()->unique()->after('medical_history');
        });

        // Backfill existing patients with unique secure tokens
        foreach (Patient::whereNull('portal_token')->get() as $patient) {
            $patient->update([
                'portal_token' => Str::lower(Str::random(16)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('portal_token');
        });
    }
};
