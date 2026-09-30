<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Patient;
use App\Models\TherapyPlan;
use App\Models\TherapySession;
use App\Models\User;
use App\Models\Visit;
use App\Models\WeightLossPlan;
use App\Models\WeightLossSession;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalOperationsReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $doctor;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::where('username', 'admin')->first();
        $this->doctor = User::where('username', 'dr_sara')->first();

        $this->patient = Patient::create([
            'name' => 'مريض تقرير العمليات',
            'phone' => '01099887766',
            'gender' => 'female',
            'age' => 32,
        ]);
    }

    public function test_can_view_operations_report(): void
    {
        // 1. Create a Visit
        $visit = Visit::create([
            'patient_id' => $this->patient->id,
            'visit_date' => Carbon::today()->toDateString(),
            'type' => 'consultation',
            'complaint' => 'ألم شديد بالركبة',
            'diagnosis' => 'خشونة بالركبة',
            'prescription' => 'مسكن وجلسات ليزر',
            'cost' => 250.00,
            'created_by_user_id' => $this->doctor->id,
        ]);

        // 2. Create an Attended Therapy Session
        $plan = TherapyPlan::create([
            'patient_id' => $this->patient->id,
            'package_name' => 'باقة علاج طبيعي تأهيلي',
            'total_sessions' => 6,
            'price' => 1200.00,
            'start_date' => Carbon::today()->toDateString(),
        ]);
        TherapySession::create([
            'patient_id' => $this->patient->id,
            'therapy_plan_id' => $plan->id,
            'session_number' => 1,
            'date' => Carbon::today()->toDateString(),
            'is_attended' => true,
            'attended_at' => Carbon::now(),
            'attended_by_user_id' => $this->doctor->id,
        ]);

        // 3. Create a Completed Weight Loss Session
        $wlPlan = WeightLossPlan::create([
            'patient_id' => $this->patient->id,
            'package_name' => 'باقة نحت القوام الماسي',
            'target_weight' => 70,
            'total_sessions' => 8,
            'cost' => 2000.00,
            'start_date' => Carbon::today()->toDateString(),
        ]);
        WeightLossSession::create([
            'patient_id' => $this->patient->id,
            'weight_loss_plan_id' => $wlPlan->id,
            'session_number' => 1,
            'session_date' => Carbon::today()->toDateString(),
            'weight' => 82.5,
            'is_completed' => true,
            'completed_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/reports/operations');
        $response->assertStatus(200);
        $response->assertSee('تقرير العمليات والحركات الطبية');
        $response->assertSee('مريض تقرير العمليات');
        $response->assertSee('خشونة بالركبة');
        $response->assertSee('جلسة علاج طبيعي');
        $response->assertSee('جلسة تخسيس ونحت قوام');
    }

    public function test_can_filter_operations_by_type(): void
    {
        // Visit
        Visit::create([
            'patient_id' => $this->patient->id,
            'visit_date' => Carbon::today()->toDateString(),
            'type' => 'consultation',
            'complaint' => 'شكوى كشف حصري',
            'diagnosis' => 'تشخيص كشف حصري',
            'cost' => 200.00,
            'created_by_user_id' => $this->doctor->id,
        ]);

        // Therapy session
        $plan = TherapyPlan::create([
            'patient_id' => $this->patient->id,
            'package_name' => 'باقة علاج طبيعي حصرية',
            'total_sessions' => 4,
            'price' => 800.00,
            'start_date' => Carbon::today()->toDateString(),
        ]);
        TherapySession::create([
            'patient_id' => $this->patient->id,
            'therapy_plan_id' => $plan->id,
            'session_number' => 1,
            'date' => Carbon::today()->toDateString(),
            'is_attended' => true,
            'attended_at' => Carbon::now(),
            'attended_by_user_id' => $this->doctor->id,
        ]);

        // Request only visits
        $response = $this->actingAs($this->admin)->get('/reports/operations?type=visits');
        $response->assertStatus(200);
        $response->assertSee('تشخيص كشف حصري');
        $response->assertDontSee('جلسة علاج طبيعي رقم 1');

        // Request only therapy
        $responseTherapy = $this->actingAs($this->admin)->get('/reports/operations?type=therapy');
        $responseTherapy->assertStatus(200);
        $responseTherapy->assertSee('جلسة علاج طبيعي رقم 1');
        $responseTherapy->assertDontSee('تشخيص كشف حصري');
    }

    public function test_can_filter_operations_by_staff_user(): void
    {
        // Visit created by doctor
        Visit::create([
            'patient_id' => $this->patient->id,
            'visit_date' => Carbon::today()->toDateString(),
            'type' => 'consultation',
            'complaint' => 'شكوى دكتورة سارة',
            'diagnosis' => 'كشف دكتورة سارة',
            'cost' => 200.00,
            'created_by_user_id' => $this->doctor->id,
        ]);

        // Visit created by admin
        Visit::create([
            'patient_id' => $this->patient->id,
            'visit_date' => Carbon::today()->toDateString(),
            'type' => 'consultation',
            'complaint' => 'شكوى دكتور حسام',
            'diagnosis' => 'كشف دكتور حسام المدير',
            'cost' => 300.00,
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/reports/operations?user_id={$this->doctor->id}");
        $response->assertStatus(200);
        $response->assertSee('كشف دكتورة سارة');
        $response->assertDontSee('كشف دكتور حسام المدير');
    }
}