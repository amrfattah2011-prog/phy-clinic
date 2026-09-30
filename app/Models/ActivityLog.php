<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'record_id',
        'patient_id',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getActionArabicAttribute(): string
    {
        return match ($this->action) {
            'create' => 'إضافة جديدة',
            'update' => 'تعديل بيانات',
            'delete' => 'حذف / إلغاء',
            'attendance' => 'تسجيل حضور',
            'dosage' => 'تسجيل جرعة',
            'payment' => 'تحصيل مالي',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
            default => $this->action,
        };
    }

    public function getModuleArabicAttribute(): string
    {
        return match ($this->module) {
            'patients' => 'المرضى والحالات',
            'visits' => 'الكشوفات والتشخيص',
            'therapy' => 'العلاج الطبيعي',
            'weight_loss' => 'جلسات التخسيس',
            'payments' => 'الخزينة وسندات القبض',
            'expenses' => 'المصروفات والنفقات',
            'settings' => 'إعدادات المركز',
            'staff' => 'طاقم العمل والموظفين',
            'roles' => 'الأدوار والصلاحيات',
            'auth' => 'المصادقة والأمان',
            default => $this->module,
        };
    }
}
