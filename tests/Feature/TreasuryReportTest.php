<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasuryReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::where('username', 'admin')->first();

        $this->patient = Patient::create([
            'name' => 'مريض الخزينة',
            'phone' => '01011223344',
            'gender' => 'male',
            'age' => 40,
        ]);
    }

    public function test_can_view_treasury_statement_with_correct_inflow_and_outflow(): void
    {
        // 1. Create a Payment (Inflow)
        Payment::create([
            'patient_id' => $this->patient->id,
            'receipt_number' => 'REC-TEST-001',
            'amount' => 1500.00,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'notes' => 'تحصيل باقة علاج طبيعي',
            'created_by_user_id' => $this->admin->id,
        ]);

        // 2. Create an Expense (Outflow)
        Expense::create([
            'category' => 'medical_supplies',
            'title' => 'شراء مستلزمات طبية',
            'amount' => 400.00,
            'expense_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/reports/treasury?quick_filter=today');
        $response->assertStatus(200);
        $response->assertSee('كشف حساب الخزينة');
        $response->assertSee('REC-TEST-001');
        $response->assertSee('شراء مستلزمات طبية');
        $response->assertSee('1,500.00');
        $response->assertSee('400.00');
        // Net cash = 1500 - 400 = 1100
        $response->assertSee('1,100.00');
    }

    public function test_opening_balance_is_calculated_for_prior_transactions(): void
    {
        // Transaction from yesterday
        $yesterday = Carbon::yesterday()->toDateString();
        Payment::create([
            'patient_id' => $this->patient->id,
            'receipt_number' => 'REC-PRIOR-01',
            'amount' => 1000.00,
            'payment_date' => $yesterday,
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        Expense::create([
            'category' => 'utilities',
            'title' => 'مرافق الأمس',
            'amount' => 200.00,
            'expense_date' => $yesterday,
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        // Today transaction
        Payment::create([
            'patient_id' => $this->patient->id,
            'receipt_number' => 'REC-TODAY-01',
            'amount' => 500.00,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        // Filter for today only: Opening balance should be 1000 - 200 = 800
        $todayStr = Carbon::today()->toDateString();
        $response = $this->actingAs($this->admin)->get("/reports/treasury?from_date={$todayStr}&to_date={$todayStr}");
        $response->assertStatus(200);
        $response->assertSee('800.00'); // Opening balance
        $response->assertSee('1,300.00'); // Closing balance (800 + 500)
    }

    public function test_filter_by_payment_method_works(): void
    {
        Payment::create([
            'patient_id' => $this->patient->id,
            'receipt_number' => 'REC-CASH',
            'amount' => 600.00,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        Payment::create([
            'patient_id' => $this->patient->id,
            'receipt_number' => 'REC-VISA',
            'amount' => 900.00,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'visa',
            'created_by_user_id' => $this->admin->id,
        ]);

        $responseVisa = $this->actingAs($this->admin)->get('/reports/treasury?payment_method=visa');
        $responseVisa->assertStatus(200);
        $responseVisa->assertSee('REC-VISA');
        $responseVisa->assertDontSee('REC-CASH');
    }
}