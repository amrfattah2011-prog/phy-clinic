<?php

namespace Tests\Feature;

use App\Models\ClinicSetting;
use App\Models\Disease;
use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetClinicDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_reset_clinic_data_and_preserve_users_and_settings(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // 1. Create a user and clinic setting
        $admin = User::where('username', 'admin')->first();
        ClinicSetting::updateOrCreate(['id' => 1], [
            'clinic_name' => 'عيادة د. احمد عادل',
            'phone' => '01000000000',
        ]);
        Disease::create(['name' => 'ضغط دم مرتفع']);

        // 2. Create transactional data: patient, payment, expense
        $patient = Patient::create([
            'name' => 'مريض للتجربة',
            'phone' => '01011112222',
            'gender' => 'male',
            'age' => 40,
        ]);

        Payment::create([
            'patient_id' => $patient->id,
            'receipt_number' => 'REC-TEST-99',
            'amount' => 500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $admin->id,
        ]);

        Expense::create([
            'title' => 'شراء مستلزمات طبية',
            'category' => 'مستلزمات',
            'amount' => 300,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $admin->id,
        ]);

        $this->assertDatabaseCount('patients', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('expenses', 1);

        // 3. Execute the reset command with --force
        $this->artisan('clinic:reset --force')
            ->expectsOutputToContain('تم بنجاح تصفير كافة الحسابات والمرضى')
            ->assertExitCode(0);

        // 4. Verify transactional tables are truncated
        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('expenses', 0);
        $this->assertDatabaseCount('therapy_plans', 0);
        $this->assertDatabaseCount('therapy_sessions', 0);
        $this->assertDatabaseCount('weight_loss_plans', 0);
        $this->assertDatabaseCount('weight_loss_sessions', 0);
        $this->assertDatabaseCount('visits', 0);

        // 5. Verify master data is PRESERVED
        $this->assertDatabaseHas('users', ['username' => 'admin']);
        $this->assertDatabaseHas('clinic_settings', ['clinic_name' => 'عيادة د. احمد عادل']);
        $this->assertDatabaseHas('diseases', ['name' => 'ضغط دم مرتفع']);
    }
}
