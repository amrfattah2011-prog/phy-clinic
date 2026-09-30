<?php

namespace Tests\Feature;

use App\Models\ClinicSetting;
use App\Models\Patient;
use App\Models\TherapyPlan;
use App\Models\User;
use App\Models\WeightLossPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::where('username', 'admin')->first();

        ClinicSetting::create([
            'clinic_name' => 'عيادة الشفاء للعلاج الطبيعي والتخسيس',
            'phone' => '01000000000',
            'address' => 'القاهرة - مصر',
            'currency' => 'EGP',
        ]);

        $this->patient = Patient::create([
            'name' => 'محمود إبراهيم الشناوي',
            'phone' => '01012345678',
            'gender' => 'male',
            'age' => 35,
        ]);
    }

    public function test_patient_has_portal_token_generated_automatically(): void
    {
        $this->assertNotEmpty($this->patient->portal_token);
        $this->assertGreaterThanOrEqual(16, strlen($this->patient->portal_token));
        $this->assertStringContainsString('/p/' . $this->patient->portal_token, $this->patient->portal_url);
    }

    public function test_whatsapp_phone_accessor_formats_egyptian_numbers(): void
    {
        $this->assertEquals('201012345678', $this->patient->whatsapp_phone);

        $foreignPatient = new Patient(['phone' => '+966501234567']);
        $this->assertEquals('966501234567', $foreignPatient->whatsapp_phone);
    }

    public function test_guest_can_access_patient_portal_with_valid_token(): void
    {
        $plan = \App\Models\TherapyPlan::create([
            'patient_id' => $this->patient->id,
            'package_name' => 'باقة علاج طبيعي مكثف',
            'total_sessions' => 12,
            'cost' => 1800,
            'status' => 'active',
            'notes' => 'انزلاق غضروفي قطني',
        ]);

        $response = $this->get('/p/' . $this->patient->portal_token);

        $response->assertStatus(200);
        $response->assertSee('محمود إبراهيم الشناوي');
        $response->assertSee('عيادة الشفاء للعلاج الطبيعي والتخسيس');
        $response->assertSee('باقة علاج طبيعي مكثف');
        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('بوابة المريض الرقمية');
    }

    public function test_invalid_portal_token_returns_404(): void
    {
        $response = $this->get('/p/non-existent-token-123456');
        $response->assertStatus(404);
    }

    public function test_patient_show_view_contains_whatsapp_and_portal_elements(): void
    {
        $response = $this->actingAs($this->admin)->get("/patients/{$this->patient->id}");

        $response->assertStatus(200);
        $response->assertSee('إرسال رسالة واتساب WhatsApp');
        $response->assertSee($this->patient->portal_url);
        $response->assertSee('رابط البوابة الرقمية الخاص بالمريض');
        $response->assertSee('waTemplates');
        $response->assertSee('sendViaWhatsapp');
    }
}
