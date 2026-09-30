<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::where('username', 'admin')->first();
        $this->receptionist = User::where('username', 'reception')->first();
    }

    public function test_guest_cannot_access_expenses(): void
    {
        $response = $this->get('/expenses');
        $response->assertRedirect('/login');
    }

    public function test_user_with_permission_can_view_expenses_index(): void
    {
        Expense::create([
            'category' => 'utilities',
            'title' => 'فاتورة كهرباء سبتمبر',
            'amount' => 450.00,
            'expense_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/expenses');
        $response->assertStatus(200);
        $response->assertSee('فاتورة كهرباء سبتمبر');
        $response->assertSee('450.00');
    }

    public function test_can_create_expense_and_records_activity_log(): void
    {
        $response = $this->actingAs($this->admin)->post('/expenses', [
            'category' => 'medical_supplies',
            'title' => 'شراء كحول ومستلزمات علاج طبيعي',
            'amount' => 1200.50,
            'expense_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'receipt_reference' => 'INV-8899',
            'notes' => 'مشتريات طبية طارئة',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'title' => 'شراء كحول ومستلزمات علاج طبيعي',
            'amount' => 1200.50,
            'category' => 'medical_supplies',
            'created_by_user_id' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'expenses',
            'action' => 'create',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_can_update_expense(): void
    {
        $expense = Expense::create([
            'category' => 'maintenance',
            'title' => 'صيانة جهاز ليزر',
            'amount' => 500.00,
            'expense_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->put("/expenses/{$expense->id}", [
            'category' => 'maintenance',
            'title' => 'صيانة جهاز ليزر وكافيتيشن',
            'amount' => 750.00,
            'expense_date' => Carbon::today()->toDateString(),
            'payment_method' => 'visa',
            'receipt_reference' => 'REC-112',
            'notes' => 'تم تغيير قطعة غيار',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'title' => 'صيانة جهاز ليزر وكافيتيشن',
            'amount' => 750.00,
            'payment_method' => 'visa',
        ]);
    }

    public function test_can_delete_expense(): void
    {
        $expense = Expense::create([
            'category' => 'other',
            'title' => 'بند تجريبي للحذف',
            'amount' => 100.00,
            'expense_date' => Carbon::today()->toDateString(),
            'payment_method' => 'cash',
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/expenses/{$expense->id}");
        $response->assertRedirect('/expenses');
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'expenses',
            'action' => 'delete',
        ]);
    }
}