<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffAndAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('تسجيل الدخول للمنظومة');
    }

    public function test_staff_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => '12345678',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Check login activity log was recorded
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'login',
            'module' => 'auth',
        ]);
    }

    public function test_staff_can_login_with_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'reception@clinic.com',
            'password' => '12345678',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_inactive_staff_cannot_login(): void
    {
        $inactiveUser = User::create([
            'name' => 'موظف موقف',
            'username' => 'suspended_user',
            'email' => 'suspended@clinic.com',
            'password' => Hash::make('12345678'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'login' => 'suspended_user',
            'password' => '12345678',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_staff_can_logout(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();

        // Check logout activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'logout',
            'module' => 'auth',
        ]);
    }

    public function test_admin_can_create_new_staff_member(): void
    {
        $admin = User::where('username', 'admin')->first();
        $doctorRole = Role::where('name', 'doctor')->first();

        $response = $this->actingAs($admin)->post('/staff', [
            'name' => 'د. خالد عبد العزيز',
            'username' => 'dr_khaled',
            'email' => 'khaled@clinic.com',
            'phone' => '01011223344',
            'role_id' => $doctorRole->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/staff');
        $this->assertDatabaseHas('users', [
            'username' => 'dr_khaled',
            'email' => 'khaled@clinic.com',
            'role_id' => $doctorRole->id,
            'is_active' => true,
        ]);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create',
            'module' => 'staff',
        ]);
    }

    public function test_admin_can_toggle_staff_active_status(): void
    {
        $admin = User::where('username', 'admin')->first();
        $reception = User::where('username', 'reception')->first();

        $this->assertTrue($reception->is_active);

        $response = $this->actingAs($admin)->post("/staff/{$reception->id}/toggle");
        $response->assertRedirect();

        $this->assertFalse($reception->fresh()->is_active);
    }

    public function test_role_and_permissions_matrix(): void
    {
        $admin = User::where('username', 'admin')->first();
        $reception = User::where('username', 'reception')->first();

        // Admin has all permissions unconditionally
        $this->assertTrue($admin->hasPermission('staff.manage'));
        $this->assertTrue($admin->hasPermission('roles.manage'));
        $this->assertTrue($admin->hasPermission('patients.view'));

        // Receptionist has patients.view and payments.create, but NOT staff.manage
        $this->assertTrue($reception->hasPermission('patients.view'));
        $this->assertTrue($reception->hasPermission('payments.create'));
        $this->assertFalse($reception->hasPermission('staff.manage'));
        $this->assertFalse($reception->hasPermission('roles.manage'));

        // If reception tries to access staff management, they get 403 Forbidden
        $forbiddenResponse = $this->actingAs($reception)->get('/staff');
        $forbiddenResponse->assertStatus(403);
    }

    public function test_actions_record_activity_logs_and_attribution(): void
    {
        $admin = User::where('username', 'admin')->first();

        // 1. Create Patient
        $patientResponse = $this->actingAs($admin)->post('/patients', [
            'name' => 'فاطمة الزهراء',
            'phone' => '01055667788',
            'gender' => 'female',
            'age' => 29,
        ]);
        $patientResponse->assertRedirect();

        $patient = \App\Models\Patient::where('phone', '01055667788')->first();
        $this->assertNotNull($patient);
        $this->assertEquals($admin->id, $patient->created_by_user_id);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'module' => 'patients',
            'action' => 'create',
            'patient_id' => $patient->id,
        ]);

        // 2. Create Visit
        $visitResponse = $this->actingAs($admin)->post("/patients/{$patient->id}/visits", [
            'visit_date' => now()->format('Y-m-d'),
            'complaint' => 'آلام بالفقرات العنقية',
            'diagnosis' => 'تشنج عضلي حاد',
            'cost' => 200.00,
        ]);
        $visitResponse->assertRedirect();

        $visit = $patient->visits()->first();
        $this->assertNotNull($visit);
        $this->assertEquals($admin->id, $visit->created_by_user_id);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'module' => 'visits',
            'action' => 'create',
            'patient_id' => $patient->id,
        ]);

        // 3. Record Payment
        $paymentResponse = $this->actingAs($admin)->post("/patients/{$patient->id}/payments", [
            'amount' => 200.00,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'visit_id' => $visit->id,
        ]);
        $paymentResponse->assertRedirect();

        $payment = $patient->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals($admin->id, $payment->created_by_user_id);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'module' => 'payments',
            'action' => 'payment',
            'patient_id' => $patient->id,
        ]);

        // 4. View Activity Logs report
        $logsResponse = $this->actingAs($admin)->get('/activity-logs');
        $logsResponse->assertStatus(200);
        $logsResponse->assertSee('سجل حركات النظام والرقابة');
        $logsResponse->assertSee('فاطمة الزهراء');
    }
}
