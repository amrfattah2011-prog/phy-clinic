<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define all permissions grouped by category
        $permissions = [
            // المرضى والحالات
            ['name' => 'patients.view', 'display_name' => 'عرض سجل وبيانات المرضى', 'category' => 'المرضى والحالات'],
            ['name' => 'patients.create', 'display_name' => 'تسجيل ملف مريض جديد', 'category' => 'المرضى والحالات'],
            ['name' => 'patients.edit', 'display_name' => 'تعديل بيانات مريض والتاريخ المرضي', 'category' => 'المرضى والحالات'],
            ['name' => 'patients.delete', 'display_name' => 'حذف ملف مريض', 'category' => 'المرضى والحالات'],

            // الكشوفات والتشخيص
            ['name' => 'visits.create', 'display_name' => 'تسجيل كشف وتشخيص طبي جديد', 'category' => 'الكشوفات والتشخيص'],
            ['name' => 'visits.delete', 'display_name' => 'حذف وإلغاء كشف مسجل', 'category' => 'الكشوفات والتشخيص'],

            // العلاج الطبيعي والأجهزة
            ['name' => 'therapy.create_plan', 'display_name' => 'إنشاء باقات العلاج الطبيعي وتحديد الجلسات', 'category' => 'العلاج الطبيعي والأجهزة'],
            ['name' => 'therapy.attendance', 'display_name' => 'تسجيل حضور الجلسات وتحديد الأجهزة المستخدمة', 'category' => 'العلاج الطبيعي والأجهزة'],
            ['name' => 'therapy.delete', 'display_name' => 'حذف وإلغاء باقة علاج طبيعي', 'category' => 'العلاج الطبيعي والأجهزة'],

            // جلسات وباقات التخسيس
            ['name' => 'weight_loss.create_plan', 'display_name' => 'إنشاء باقات التخسيس ونحت القوام', 'category' => 'جلسات وباقات التخسيس'],
            ['name' => 'weight_loss.attendance', 'display_name' => 'تسجيل إتمام جلسات وجرعات التخسيس وتحديد الأجهزة', 'category' => 'جلسات وباقات التخسيس'],
            ['name' => 'weight_loss.delete', 'display_name' => 'حذف باقة أو جلسة تخسيس', 'category' => 'جلسات وباقات التخسيس'],

            // الخزينة والحسابات وسندات القبض
            ['name' => 'payments.create', 'display_name' => 'تسجيل سندات قبض وتحصيل وطباعة إيصالات', 'category' => 'الخزينة والمدفوعات'],
            ['name' => 'payments.delete', 'display_name' => 'إلغاء وحذف سند قبض', 'category' => 'الخزينة والمدفوعات'],
            ['name' => 'financial.reports', 'display_name' => 'الاطلاع على تقارير الإيرادات اليومية والمالية', 'category' => 'الخزينة والمدفوعات'],

            // إدارة النظام والموظفين
            ['name' => 'settings.manage', 'display_name' => 'تعديل بيانات وهوية المركز وقوائم الأجهزة والأمراض', 'category' => 'إدارة النظام والموظفين'],
            ['name' => 'staff.manage', 'display_name' => 'إدارة الموظفين وكلمات المرور والحسابات', 'category' => 'إدارة النظام والموظفين'],
            ['name' => 'roles.manage', 'display_name' => 'إدارة الأدوار الوظيفية ومصفوفة الصلاحيات', 'category' => 'إدارة النظام والموظفين'],
            ['name' => 'activity_logs.view', 'display_name' => 'الاطلاع على سجل حركات النظام (مين عمل إيه)', 'category' => 'إدارة النظام والموظفين'],

            // المصروفات والخزينة والتقارير
            ['name' => 'expenses.view', 'display_name' => 'عرض سجل المصروفات والنفقات', 'category' => 'الخزينة والماليات'],
            ['name' => 'expenses.create', 'display_name' => 'تسجيل وإضافة بند مصروف جديد', 'category' => 'الخزينة والماليات'],
            ['name' => 'expenses.delete', 'display_name' => 'حذف أو تعديل بند مصروف', 'category' => 'الخزينة والماليات'],
            ['name' => 'treasury.statement', 'display_name' => 'الاطلاع على كشف حساب الخزينة وصافي الإيرادات والمصروفات', 'category' => 'الخزينة والماليات'],
            ['name' => 'reports.operations', 'display_name' => 'الاطلاع على تقرير العمليات والكشوفات والجلسات', 'category' => 'التقارير والإحصائيات'],
            ['name' => 'reports.weight_loss', 'display_name' => 'الاطلاع على تقرير باقات التخسيس ونسب التحصيل وتكلفة الجلسات', 'category' => 'التقارير والإحصائيات'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['name']] = Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['display_name' => $perm['display_name'], 'category' => $perm['category']]
            );
        }

        // 2. Define Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'مدير النظام (كامل الصلاحيات)',
            'description' => 'يمتلك كافة الصلاحيات لإدارة المركز والماليات والموظفين وسجل الحركات.',
            'is_system' => true,
        ]);

        $doctorRole = Role::firstOrCreate(['name' => 'doctor'], [
            'display_name' => 'طبيب معالج / استشاري',
            'description' => 'صلاحية فحص وتشخيص المرضى، إنشاء الباقات، ومتابعة الجلسات الطبية.',
            'is_system' => false,
        ]);

        $therapistRole = Role::firstOrCreate(['name' => 'therapist'], [
            'display_name' => 'أخصائي علاج طبيعي وتأهيل',
            'description' => 'تسجيل حضور الجلسات وتحديد الأجهزة المستخدمة ومتابعة المرضى.',
            'is_system' => false,
        ]);

        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist'], [
            'display_name' => 'موظف استقبال (Front-Desk)',
            'description' => 'تسجيل الحالات الجديدة، تسجيل الكشوفات، تحصيل الدفعات، وطباعة الإيصالات.',
            'is_system' => false,
        ]);

        $accountantRole = Role::firstOrCreate(['name' => 'accountant'], [
            'display_name' => 'محاسب مالي',
            'description' => 'إدارة سندات القبض ومتابعة كشوفات الحساب والتقارير المالية.',
            'is_system' => false,
        ]);

        // 3. Assign permissions to roles
        // Admin gets all permissions
        $adminRole->permissions()->sync(array_values(array_map(fn($p) => $p->id, $permissionModels)));

        // Doctor permissions
        $doctorRole->permissions()->sync([
            $permissionModels['patients.view']->id,
            $permissionModels['patients.create']->id,
            $permissionModels['patients.edit']->id,
            $permissionModels['visits.create']->id,
            $permissionModels['therapy.create_plan']->id,
            $permissionModels['therapy.attendance']->id,
            $permissionModels['weight_loss.create_plan']->id,
            $permissionModels['weight_loss.attendance']->id,
            $permissionModels['reports.operations']->id,
            $permissionModels['reports.weight_loss']->id,
            $permissionModels['activity_logs.view']->id,
        ]);

        // Therapist permissions
        $therapistRole->permissions()->sync([
            $permissionModels['patients.view']->id,
            $permissionModels['therapy.attendance']->id,
            $permissionModels['weight_loss.attendance']->id,
            $permissionModels['reports.operations']->id,
        ]);

        // Receptionist permissions
        $receptionistRole->permissions()->sync([
            $permissionModels['patients.view']->id,
            $permissionModels['patients.create']->id,
            $permissionModels['patients.edit']->id,
            $permissionModels['visits.create']->id,
            $permissionModels['therapy.attendance']->id,
            $permissionModels['weight_loss.attendance']->id,
            $permissionModels['payments.create']->id,
            $permissionModels['expenses.create']->id,
            $permissionModels['reports.operations']->id,
        ]);

        // Accountant permissions
        $accountantRole->permissions()->sync([
            $permissionModels['patients.view']->id,
            $permissionModels['payments.create']->id,
            $permissionModels['payments.delete']->id,
            $permissionModels['financial.reports']->id,
            $permissionModels['expenses.view']->id,
            $permissionModels['expenses.create']->id,
            $permissionModels['expenses.delete']->id,
            $permissionModels['treasury.statement']->id,
            $permissionModels['reports.operations']->id,
            $permissionModels['reports.weight_loss']->id,
            $permissionModels['activity_logs.view']->id,
        ]);

        // 4. Create or update default staff users
        // Default Super Admin User
        $adminUser = User::where('email', 'admin@clinic.com')->orWhere('username', 'admin')->first();
        if ($adminUser) {
            $adminUser->update([
                'username' => 'admin',
                'name' => 'د. حسام الشريف (مدير النظام)',
                'phone' => '01012345678',
                'password' => Hash::make('12345678'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]);
        } else {
            $adminUser = User::create([
                'name' => 'د. حسام الشريف (مدير النظام)',
                'username' => 'admin',
                'email' => 'admin@clinic.com',
                'phone' => '01012345678',
                'password' => Hash::make('12345678'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]);
        }

        // Doctor User
        User::firstOrCreate(
            ['username' => 'dr_sara'],
            [
                'name' => 'د. سارة محمود',
                'email' => 'sara@clinic.com',
                'phone' => '01122334455',
                'password' => Hash::make('12345678'),
                'role_id' => $doctorRole->id,
                'is_active' => true,
            ]
        );

        // Receptionist User
        User::firstOrCreate(
            ['username' => 'reception'],
            [
                'name' => 'أحمد هاني (استقبال)',
                'email' => 'reception@clinic.com',
                'phone' => '01233445566',
                'password' => Hash::make('12345678'),
                'role_id' => $receptionistRole->id,
                'is_active' => true,
            ]
        );

        // 5. Initial Sample Activity Logs
        if (\App\Models\ActivityLog::count() === 0) {
            $p1 = \App\Models\Patient::first();
            $patientId = $p1 ? $p1->id : null;
            $patientName = $p1 ? $p1->name : 'محمود عبد الرحيم';

            \App\Models\ActivityLog::create([
                'user_id' => $adminUser->id,
                'action' => 'login',
                'module' => 'auth',
                'description' => "قام الموظف '{$adminUser->name}' بتسجيل الدخول إلى النظام",
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(4),
            ]);

            \App\Models\ActivityLog::create([
                'user_id' => $adminUser->id,
                'action' => 'create',
                'module' => 'patients',
                'description' => "تسجيل ملف مريض جديد: '{$patientName}' (انزلاق غضروفي قطني)",
                'patient_id' => $patientId,
                'record_id' => $patientId,
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(3)->subMinutes(40),
            ]);

            $reception = User::where('username', 'reception')->first();
            if ($reception) {
                \App\Models\ActivityLog::create([
                    'user_id' => $reception->id,
                    'action' => 'payment',
                    'module' => 'payments',
                    'description' => "تحصيل سند قبض رقم 'REC-2026-0001' بمبلغ 500.00 ج.م نقداً من المريض: '{$patientName}'",
                    'patient_id' => $patientId,
                    'record_id' => 1,
                    'ip_address' => '127.0.0.1',
                    'created_at' => now()->subHours(2)->subMinutes(15),
                ]);

                \App\Models\ActivityLog::create([
                    'user_id' => $reception->id,
                    'action' => 'attendance',
                    'module' => 'therapy',
                    'description' => "تسجيل حضور جلسة علاج طبيعي رقم 1 واستخدام أجهزة: ليزر علاجي بارد + موجات فوق صوتية للمريض: '{$patientName}'",
                    'patient_id' => $patientId,
                    'record_id' => 1,
                    'ip_address' => '127.0.0.1',
                    'created_at' => now()->subMinutes(45),
                ]);
            }
        }
    }
}
