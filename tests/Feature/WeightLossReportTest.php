<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Models\WeightLossPlan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightLossReportTest extends TestCase
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
            'name' => 'نور الهدى إبراهيم',
            'phone' => '01099887766',
            'gender' => 'female',
            'age' => 32,
        ]);
    }

    public function test_guest_cannot_access_weight_loss_report(): void
    {
        $response = $this->get('/reports/weight-loss');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_report_with_calculated_session_cost_and_collection_rates(): void
    {
        // 1. Create a Weight Loss Plan: 8 sessions for 2,400 EGP (Unit session cost = 300 EGP)
        $plan = $this->patient->weightLossPlans()->create([
            'package_name' => 'باقة نحت القوام الماسي',
            'total_sessions' => 8,
            'cost' => 2400.00,
            'status' => 'active',
            'notes' => 'جلسات كافيتيشن وفاكيوم وتخسيس موضعي',
        ]);

        // 2. Create a Payment of 1,200 EGP directly linked to this plan (50% collection, 4 sessions paid, 4 sessions remaining)
        Payment::create([
            'patient_id' => $this->patient->id,
            'weight_loss_plan_id' => $plan->id,
            'receipt_number' => 'REC-WL-001',
            'amount' => 1200.00,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'notes' => 'دفعة أولى 50%',
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/reports/weight-loss');

        $response->assertStatus(200);
        $response->assertSee('تقرير باقات التخسيس ومتابعة التحصيل وتكلفة الجلسات');
        $response->assertSee('نور الهدى إبراهيم');
        $response->assertSee('باقة نحت القوام الماسي');
        $response->assertSee('2,400.00'); // Total cost
        $response->assertSee('300.00');   // Unit session cost (2400 / 8)
        $response->assertSee('1,200.00'); // Collected amount
        $response->assertSee('50%');      // Collection rate
        $response->assertSee('4');        // Paid sessions
        $response->assertSee('الجرعات الفعلية');
        $response->assertSee('الجرعات المتبقية التي لم تنفذ بعد');
    }

    public function test_can_filter_report_by_collection_status_and_search(): void
    {
        // Plan 1: Fully Paid (1000 / 1000)
        $plan1 = $this->patient->weightLossPlans()->create([
            'package_name' => 'باقة التخسيس السريع',
            'total_sessions' => 5,
            'cost' => 1000.00,
            'status' => 'active',
        ]);
        Payment::create([
            'patient_id' => $this->patient->id,
            'weight_loss_plan_id' => $plan1->id,
            'receipt_number' => 'REC-WL-101',
            'amount' => 1000.00,
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        // Plan 2: Unpaid (0 / 3000) for another patient
        $patient2 = Patient::create([
            'name' => 'أحمد حسام التخسيس',
            'phone' => '01122334455',
            'gender' => 'male',
            'age' => 45,
        ]);
        $patient2->weightLossPlans()->create([
            'package_name' => 'باقة إذابة الدهون',
            'total_sessions' => 6,
            'cost' => 3000.00,
            'status' => 'active',
        ]);

        // Filter: only fully paid
        $responseFullyPaid = $this->actingAs($this->admin)->get('/reports/weight-loss?collection_status=fully_paid');
        $responseFullyPaid->assertStatus(200);
        $responseFullyPaid->assertSee('باقة التخسيس السريع');
        $responseFullyPaid->assertDontSee('باقة إذابة الدهون');

        // Filter: only unpaid
        $responseUnpaid = $this->actingAs($this->admin)->get('/reports/weight-loss?collection_status=unpaid');
        $responseUnpaid->assertStatus(200);
        $responseUnpaid->assertSee('باقة إذابة الدهون');
        $responseUnpaid->assertDontSee('باقة التخسيس السريع');

        // Search by patient name
        $responseSearch = $this->actingAs($this->admin)->get('/reports/weight-loss?search=أحمد حسام');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('أحمد حسام التخسيس');
        $responseSearch->assertDontSee('نور الهدى إبراهيم');
    }

    public function test_can_render_official_print_view(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/weight-loss?print=1');
        $response->assertStatus(200);
        $response->assertSee('تقرير باقات التخسيس ونحت القوام ونسب التحصيل');
        $response->assertSee('اعتماد الإدارة والمدير الطبي');
    }
}