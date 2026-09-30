<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\TherapyPlan;
use App\Models\TherapySession;
use App\Models\Visit;
use App\Models\WeightLossSession;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = \App\Models\User::where('username', 'admin')->first();
        $this->actingAs($admin);
    }

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم اليومية');
    }

    public function test_can_create_patient(): void
    {
        $response = $this->post('/patients', [
            'name' => 'طارق كمال',
            'phone' => '01099887766',
            'gender' => 'male',
            'age' => 29,
            'medical_history' => 'ألم بالفقرات القطنية',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('patients', [
            'name' => 'طارق كمال',
            'phone' => '01099887766',
        ]);
    }

    public function test_can_create_therapy_plan_and_auto_generates_sessions(): void
    {
        $patient = Patient::create([
            'name' => 'سلمى عبد العزيز',
            'phone' => '01200112233',
            'gender' => 'female',
            'age' => 34,
        ]);

        $response = $this->post("/patients/{$patient->id}/therapy-plans", [
            'package_name' => 'باقة تقوية عضلات الظهر',
            'total_sessions' => 6,
            'cost' => 1200.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('therapy_plans', [
            'patient_id' => $patient->id,
            'package_name' => 'باقة تقوية عضلات الظهر',
            'total_sessions' => 6,
        ]);

        // Auto generated 6 sessions
        $this->assertCount(6, $patient->therapySessions);
    }

    public function test_can_toggle_therapy_session_attendance_via_ajax(): void
    {
        $patient = Patient::create([
            'name' => 'أحمد هاني',
            'phone' => '01000000000',
            'gender' => 'male',
            'age' => 40,
        ]);

        $plan = TherapyPlan::create([
            'patient_id' => $patient->id,
            'package_name' => 'باقة تأهيل',
            'total_sessions' => 3,
            'cost' => 600.00,
            'status' => 'active',
        ]);

        $session = TherapySession::create([
            'therapy_plan_id' => $plan->id,
            'patient_id' => $patient->id,
            'session_number' => 1,
            'session_date' => Carbon::today(),
            'is_attended' => false,
        ]);

        $response = $this->postJson("/therapy-sessions/{$session->id}/toggle");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_attended' => true,
            'completed_count' => 1,
            'remaining_count' => 2,
        ]);

        $this->assertTrue($session->fresh()->is_attended);
        $this->assertNotNull($session->fresh()->attended_at);
    }

    public function test_can_create_weight_loss_session_and_toggle_dosage(): void
    {
        $patient = Patient::create([
            'name' => 'ريم أحمد',
            'phone' => '01111111111',
            'gender' => 'female',
            'age' => 27,
        ]);

        $response = $this->post("/patients/{$patient->id}/weight-loss-sessions", [
            'session_date' => Carbon::today()->format('Y-m-d'),
            'weight' => 85.0,
            'height' => 170.0,
            'dosage_amount' => '0.6 ملغ',
            'injection_type' => 'ساكسندا (Saxenda)',
            'is_completed' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('weight_loss_sessions', [
            'patient_id' => $patient->id,
            'weight' => 85.0,
            'injection_type' => 'ساكسندا (Saxenda)',
            'is_completed' => true,
        ]);

        $session = $patient->weightLossSessions()->first();
        // Check BMI was auto calculated: 85 / (1.70 * 1.70) = 29.41
        $this->assertEquals(29.41, (float) $session->bmi);

        // Toggle back
        $toggleResponse = $this->postJson("/weight-loss-sessions/{$session->id}/toggle");
        $toggleResponse->assertStatus(200);
        $this->assertFalse($session->fresh()->is_completed);
    }

    public function test_can_record_payment_and_generate_80mm_thermal_receipt(): void
    {
        $patient = Patient::create([
            'name' => 'حسام محمود',
            'phone' => '01555555555',
            'gender' => 'male',
            'age' => 45,
        ]);

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'visit_date' => Carbon::today(),
            'complaint' => 'خشونة بالركبة',
            'diagnosis' => 'التهاب أوتار الركبة',
            'cost' => 300.00,
        ]);

        $response = $this->post("/patients/{$patient->id}/payments", [
            'amount' => 300.00,
            'payment_date' => Carbon::today()->format('Y-m-d'),
            'payment_method' => 'cash',
            'visit_id' => $visit->id,
            'notes' => 'سداد الكشف',
        ]);

        $response->assertRedirect();
        $payment = $patient->payments()->first();
        $this->assertNotNull($payment);
        $this->assertStringStartsWith('REC-', $payment->receipt_number);

        // Check thermal receipt view
        $receiptResponse = $this->get("/payments/{$payment->id}/receipt");
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee($payment->receipt_number);
        $receiptResponse->assertSee('عيادة د. أحمد عادل');
        $receiptResponse->assertSee('حسام محمود');
        $receiptResponse->assertSee('300.00 ج.م');
        $receiptResponse->assertSee('window.print()', false);
    }

    public function test_patient_financial_statement_view(): void
    {
        $patient = Patient::create([
            'name' => 'نجلاء شريف',
            'phone' => '01022233344',
            'gender' => 'female',
            'age' => 39,
        ]);

        $response = $this->get("/patients/{$patient->id}/statement");
        $response->assertStatus(200);
        $response->assertSee('كشف حساب مالي تفصيلي');
        $response->assertSee('نجلاء شريف');
    }

    public function test_patient_profile_defaults_to_visits_tab(): void
    {
        $patient = Patient::create([
            'name' => 'مروان سعيد',
            'phone' => '01011122233',
            'gender' => 'male',
            'age' => 31,
        ]);

        $response = $this->get("/patients/{$patient->id}");
        $response->assertStatus(200);
        // Assert visits tab is loaded by default
        $response->assertSee('1. الكشوفات والتشخيص الطبي');
        $response->assertSee('tab-btn-visits');
        $response->assertSee('tab-content-visits');
    }

    public function test_can_create_weight_loss_plan_and_auto_generates_sessions(): void
    {
        $patient = Patient::create([
            'name' => 'هدى فاروق',
            'phone' => '01299988877',
            'gender' => 'female',
            'age' => 28,
        ]);

        $response = $this->post("/patients/{$patient->id}/weight-loss-plans", [
            'package_name' => 'باقة التخسيس المتقدمة 8 جلسات',
            'total_sessions' => 8,
            'cost' => 2400.00,
            'notes' => 'تشمل كافيتيشن وفاكيوم مع ساكسندا',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('weight_loss_plans', [
            'patient_id' => $patient->id,
            'package_name' => 'باقة التخسيس المتقدمة 8 جلسات',
            'total_sessions' => 8,
            'cost' => 2400.00,
        ]);

        $plan = $patient->weightLossPlans()->first();
        $this->assertNotNull($plan);
        $this->assertCount(8, $plan->sessions);
    }

    public function test_can_attach_devices_to_session(): void
    {
        $patient = Patient::create([
            'name' => 'سامح وفيق',
            'phone' => '01511223344',
            'gender' => 'male',
            'age' => 45,
        ]);

        $plan = TherapyPlan::create([
            'patient_id' => $patient->id,
            'package_name' => 'باقة فقرات الرقبة',
            'total_sessions' => 4,
            'cost' => 800.00,
            'status' => 'active',
        ]);

        $session = TherapySession::create([
            'therapy_plan_id' => $plan->id,
            'patient_id' => $patient->id,
            'session_number' => 1,
            'session_date' => Carbon::today(),
            'is_attended' => true,
        ]);

        $device1 = \App\Models\ClinicDevice::create([
            'name' => 'جهاز ليزر بارد (Cold Laser)',
            'category' => 'therapy',
            'is_active' => true,
        ]);
        $device2 = \App\Models\ClinicDevice::create([
            'name' => 'جهاز موجات صوتية (Ultrasound)',
            'category' => 'therapy',
            'is_active' => true,
        ]);

        $response = $this->postJson("/therapy-sessions/{$session->id}/devices", [
            'device_ids' => [$device1->id, $device2->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertCount(2, $session->fresh()->devices);
        $this->assertTrue($session->fresh()->devices->contains($device1));
    }

    public function test_can_manage_patient_diseases_and_add_new_on_the_fly(): void
    {
        $disease1 = \App\Models\Disease::create(['name' => 'مرض السكري', 'category' => 'chronic']);

        $response = $this->post('/patients', [
            'name' => 'جمال عبد الهادي',
            'phone' => '01033344455',
            'gender' => 'male',
            'age' => 55,
            'disease_ids' => [$disease1->id],
            'new_disease_name' => 'قصور الغدة الدرقية',
        ]);

        $response->assertRedirect();
        $patient = Patient::where('phone', '01033344455')->first();
        $this->assertNotNull($patient);

        // Check new disease was created
        $this->assertDatabaseHas('diseases', ['name' => 'قصور الغدة الدرقية']);
        // Check both diseases are attached
        $this->assertCount(2, $patient->diseases);
    }

    public function test_can_update_clinic_settings_and_reflect_on_receipt(): void
    {
        $response = $this->put('/settings', [
            'clinic_name' => 'مركز النور التخصصي للعلاج الطبيعي',
            'doctor_name' => 'أ.د. حسام الشريف',
            'doctor_title' => 'استشاري العلاج الطبيعي والتأهيل الحركي',
            'phone_1' => '01001234567',
            'address' => 'القاهرة - مدينة نصر',
            'currency' => 'ج.م',
            'receipt_footer' => 'شكراً لاختياركم مركز النور - نتمنى لكم الشفاء العاجل',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clinic_settings', [
            'clinic_name' => 'مركز النور التخصصي للعلاج الطبيعي',
            'doctor_name' => 'أ.د. حسام الشريف',
        ]);

        // Create a patient and payment to test receipt rendering with updated settings
        $patient = Patient::create([
            'name' => 'ماجد خالد',
            'phone' => '01234567890',
            'gender' => 'male',
            'age' => 42,
        ]);

        $payment = Payment::create([
            'patient_id' => $patient->id,
            'receipt_number' => 'REC-2026-TEST',
            'amount' => 500.00,
            'payment_date' => Carbon::today(),
            'payment_method' => 'cash',
        ]);

        $receiptResponse = $this->get("/payments/{$payment->id}/receipt");
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('مركز النور التخصصي للعلاج الطبيعي');
        $receiptResponse->assertSee('أ.د. حسام الشريف');
        $receiptResponse->assertSee('شكراً لاختياركم مركز النور');
    }

    public function test_patient_whatsapp_link_and_messaging_features(): void
    {
        $patient = Patient::create([
            'name' => 'مصطفى عادل',
            'phone' => '01012345678',
            'gender' => 'male',
            'age' => 30,
        ]);

        // Test Egyptian 11-digit mobile formatting
        $this->assertEquals('201012345678', $patient->whatsapp_phone);
        $this->assertEquals('https://wa.me/201012345678', $patient->whatsapp_link);

        // Test Gulf/Saudi mobile formatting
        $saudiPatient = Patient::create([
            'name' => 'فيصل الحربي',
            'phone' => '0501234567',
            'gender' => 'male',
            'age' => 35,
        ]);
        $this->assertEquals('966501234567', $saudiPatient->whatsapp_phone);

        // Test index view shows WhatsApp icon
        $indexResponse = $this->get('/patients');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('https://wa.me/201012345678');

        // Test show view contains WhatsApp modal and templates
        $showResponse = $this->get("/patients/{$patient->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('whatsapp-modal');
        $showResponse->assertSee('openWhatsappModal');
        $showResponse->assertSee('sendViaWhatsapp');

        // Test thermal receipt contains WhatsApp sharing link
        $payment = Payment::create([
            'patient_id' => $patient->id,
            'receipt_number' => 'REC-WA-TEST',
            'amount' => 300.00,
            'payment_date' => Carbon::today(),
            'payment_method' => 'cash',
        ]);
        $receiptResponse = $this->get("/payments/{$payment->id}/receipt");
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('https://wa.me/201012345678');

        // Test statement contains WhatsApp sharing link
        $statementResponse = $this->get("/patients/{$patient->id}/statement");
        $statementResponse->assertStatus(200);
        $statementResponse->assertSee('https://wa.me/201012345678');
    }
}
