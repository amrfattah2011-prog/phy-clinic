<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MedicalReportController;
use App\Http\Controllers\PatientAttachmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TherapyPlanController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\TherapySessionController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\WeightLossPlanController;
use App\Http\Controllers\WeightLossReportController;
use App\Http\Controllers\WeightLossSessionController;
use Illuminate\Support\Facades\Route;

// Public Patient Portal (Accessible via WhatsApp Link)
Route::get('/p/{token}', [PatientPortalController::class, 'show'])->name('patient.portal');

// Authentication Routes (Guests)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Clinic Routes
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Daily Clinic Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Patients Management
    Route::resource('patients', PatientController::class);
    Route::post('/patients/{patient}/diseases', [PatientController::class, 'updateDiseases'])->name('patients.diseases.update');

    // Patient Attachments & Medical Documents (مرفقات المريض، الأشعة، والتقارير الطبية)
    Route::post('/patients/{patient}/attachments', [PatientAttachmentController::class, 'store'])->name('patients.attachments.store');
    Route::get('/patients/{patient}/attachments/{attachment}/view', [PatientAttachmentController::class, 'view'])->name('patients.attachments.view');
    Route::get('/patients/{patient}/attachments/{attachment}/download', [PatientAttachmentController::class, 'download'])->name('patients.attachments.download');
    Route::delete('/patients/{patient}/attachments/{attachment}', [PatientAttachmentController::class, 'destroy'])->name('patients.attachments.destroy');

    // Consultations & Visits (الكشف الطبي)
    Route::post('/patients/{patient}/visits', [VisitController::class, 'store'])->name('visits.store');
    Route::delete('/visits/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy');

    // Physical Therapy Plans & Sessions (العلاج الطبيعي والأجهزة)
    Route::post('/patients/{patient}/therapy-plans', [TherapyPlanController::class, 'store'])->name('therapy-plans.store');
    Route::delete('/therapy-plans/{plan}', [TherapyPlanController::class, 'destroy'])->name('therapy-plans.destroy');
    Route::post('/therapy-sessions/{session}/toggle', [TherapySessionController::class, 'toggleAttendance'])->name('therapy-sessions.toggle');
    Route::put('/therapy-sessions/{session}', [TherapySessionController::class, 'update'])->name('therapy-sessions.update');
    Route::post('/therapy-sessions/{session}/devices', [TherapySessionController::class, 'updateDevices'])->name('therapy-sessions.devices.update');

    // Weight Loss Plans & Sessions (باقات وجلسات التخسيس)
    Route::post('/patients/{patient}/weight-loss-plans', [WeightLossPlanController::class, 'store'])->name('weight-loss-plans.store');
    Route::delete('/weight-loss-plans/{plan}', [WeightLossPlanController::class, 'destroy'])->name('weight-loss-plans.destroy');
    Route::post('/patients/{patient}/weight-loss-sessions', [WeightLossSessionController::class, 'store'])->name('weight-loss-sessions.store');
    Route::put('/weight-loss-sessions/{session}', [WeightLossSessionController::class, 'update'])->name('weight-loss-sessions.update');
    Route::post('/weight-loss-sessions/{session}/toggle', [WeightLossSessionController::class, 'toggleCompleted'])->name('weight-loss-sessions.toggle');
    Route::post('/weight-loss-sessions/{session}/devices', [WeightLossSessionController::class, 'updateDevices'])->name('weight-loss-sessions.devices.update');
    Route::post('/weight-loss-sessions/{session}/attachment', [WeightLossSessionController::class, 'uploadAttachment'])->name('weight-loss-sessions.attachment.store');
    Route::get('/weight-loss-sessions/{session}/attachment/view', [WeightLossSessionController::class, 'viewAttachment'])->name('weight-loss-sessions.attachment.view');
    Route::get('/weight-loss-sessions/{session}/attachment/download', [WeightLossSessionController::class, 'downloadAttachment'])->name('weight-loss-sessions.attachment.download');
    Route::delete('/weight-loss-sessions/{session}/attachment', [WeightLossSessionController::class, 'destroyAttachment'])->name('weight-loss-sessions.attachment.destroy');
    Route::delete('/weight-loss-sessions/{session}', [WeightLossSessionController::class, 'destroy'])->name('weight-loss-sessions.destroy');

    // Financial Ledger, Payments & Thermal Receipts
    Route::post('/patients/{patient}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'showReceipt'])->name('payments.receipt');
    Route::get('/patients/{patient}/statement', [PaymentController::class, 'printStatement'])->name('patients.statement');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Staff Management (طاقم العمل والموظفين)
    Route::middleware('permission:staff.manage')->group(function () {
        Route::resource('staff', StaffController::class);
        Route::post('/staff/{staff}/toggle', [StaffController::class, 'toggleActive'])->name('staff.toggle');
    });

    // Roles & Permissions (الأدوار ومصفوفة الصلاحيات)
    Route::middleware('permission:roles.manage')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Audit Trail & Activity Logs (سجل حركات النظام - مين عمل كل حركة)
    Route::middleware('permission:activity_logs.view')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Clinic Settings & Catalogs (بيانات المركز، قائمة الأجهزة، قائمة الأمراض)
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings', [ClinicSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [ClinicSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/diseases', [ClinicSettingController::class, 'storeDisease'])->name('settings.diseases.store');
        Route::delete('/settings/diseases/{disease}', [ClinicSettingController::class, 'destroyDisease'])->name('settings.diseases.destroy');
        Route::post('/settings/devices', [ClinicSettingController::class, 'storeDevice'])->name('settings.devices.store');
        Route::delete('/settings/devices/{device}', [ClinicSettingController::class, 'destroyDevice'])->name('settings.devices.destroy');
    });

    // Expenses Management (شاشة وإدارة المصروفات)
    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create')->middleware('permission:expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store')->middleware('permission:expenses.create');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit')->middleware('permission:expenses.delete');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update')->middleware('permission:expenses.delete');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('permission:expenses.delete');
    });

    // Medical Operations Report (تقرير العمليات والكشوفات وجلسات العلاج الطبيعي والتخسيس)
    Route::middleware('permission:reports.operations')->group(function () {
        Route::get('/reports/operations', [MedicalReportController::class, 'operations'])->name('reports.operations');
    });

    // Treasury / Safe Statement & Cash Flow (كشف حساب الخزينة وصافي الإيرادات والمصروفات)
    Route::middleware('permission:treasury.statement')->group(function () {
        Route::get('/reports/treasury', [TreasuryController::class, 'statement'])->name('reports.treasury');
    });

    // Weight Loss Packages Collection Report (تقرير باقات التخسيس ونسب التحصيل وتكلفة الجلسات)
    Route::middleware('permission:reports.weight_loss')->group(function () {
        Route::get('/reports/weight-loss', [WeightLossReportController::class, 'index'])->name('reports.weight_loss');
    });

});
