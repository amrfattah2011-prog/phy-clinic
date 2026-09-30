<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'therapy_plan_id',
        'weight_loss_plan_id',
        'visit_id',
        'receipt_number',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'created_by_user_id',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->receipt_number)) {
                $datePrefix = date('Ymd');
                // نولّد رقماً آمناً بدون race condition: تاريخ + عداد يومي من DB
                $todayCount = static::whereDate('created_at', today())->count() + 1;
                $uniqueSuffix = strtoupper(substr(uniqid(), -4)); // طابع فريد إضافي للأمان
                $model->receipt_number = 'REC-' . $datePrefix . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT) . $uniqueSuffix;
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function therapyPlan(): BelongsTo
    {
        return $this->belongsTo(TherapyPlan::class);
    }

    public function weightLossPlan(): BelongsTo
    {
        return $this->belongsTo(WeightLossPlan::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function getPaymentMethodArabicAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'نقداً (كاش)',
            'vodafone_cash' => 'فودافون كاش / محفظة',
            'visa'          => 'بطاقة بنكية / فيزا',
            'bank_transfer' => 'تحويل بنكي',
            default         => $this->payment_method,
        };
    }

    /**
     * اسم وسيلة الدفع مع رمز (مستخدم في كشف الخزينة والتقارير)
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'نقدي 💵',
            'visa'          => 'فيزا/بنك 💳',
            'vodafone_cash' => 'فودافون كاش 📱',
            'bank_transfer' => 'تحويل بنكي 🏦',
            default         => $this->payment_method,
        };
    }
}
