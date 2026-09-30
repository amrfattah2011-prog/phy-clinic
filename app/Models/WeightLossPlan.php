<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeightLossPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'package_name',
        'total_sessions',
        'cost',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_sessions' => 'integer',
        'cost' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WeightLossSession::class)->orderBy('session_number', 'asc')->orderBy('session_date', 'asc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getCompletedSessionsCountAttribute(): int
    {
        return $this->sessions()->where('is_completed', true)->count();
    }

    public function getRemainingSessionsCountAttribute(): int
    {
        return max(0, $this->total_sessions - $this->completed_sessions_count);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_sessions <= 0) {
            return 0;
        }
        return min(100, (int) round(($this->completed_sessions_count / $this->total_sessions) * 100));
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'active' => 'سارية',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
            default => $this->status,
        };
    }

    /**
     * تكلفة الجلسة الواحدة داخل الباقة (مبلغ الباقة مقسوماً على عدد الجلسات)
     */
    public function getSessionCostAttribute(): float
    {
        if ($this->total_sessions <= 0) {
            return 0.0;
        }
        return round((float) ($this->cost / $this->total_sessions), 2);
    }

    /**
     * إجمالي المبلغ المحصل من الباقة (دفعات مباشرة + التحصيلات المرتبطة)
     */
    public function getCollectedAmountAttribute(): float
    {
        // 1. الدفعات المرتبطة مباشرة بهذه الباقة
        $direct = (float) $this->payments()->sum('amount');
        if ($direct > 0) {
            return min((float) $this->cost, $direct);
        }

        // 2. إذا لم توجد دفعات محددة بالباقة، فحص مدفوعات المريض غير المخصصة
        if ($this->patient) {
            $unassignedPayments = (float) $this->patient->payments()
                ->whereNull('therapy_plan_id')
                ->whereNull('weight_loss_plan_id')
                ->whereNull('visit_id')
                ->sum('amount');

            if ($unassignedPayments > 0) {
                // إذا كان المريض ليس لديه باقات علاج طبيعي وباقة تخسيس واحدة
                $otherPlansCount = $this->patient->weightLossPlans()->where('id', '!=', $this->id)->count()
                    + $this->patient->therapyPlans()->count();
                if ($otherPlansCount === 0) {
                    return min((float) $this->cost, $unassignedPayments);
                }
            }
        }

        return 0.0;
    }

    /**
     * المبلغ المتبقي غير المحصل من الباقة
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) ($this->cost - $this->collected_amount));
    }

    /**
     * نسبة التحصيل المئوية للباقة (%)
     */
    public function getCollectionRateAttribute(): float
    {
        if ($this->cost <= 0) {
            return 0.0;
        }
        return min(100.0, round(($this->collected_amount / $this->cost) * 100, 1));
    }

    /**
     * عدد الجلسات التي تم تحصيل قيمتها فعلياً (المبلغ المحصل ÷ سعر الجلسة بالباقة)
     */
    public function getPaidSessionsCountAttribute(): float
    {
        $sessionCost = $this->session_cost;
        if ($sessionCost <= 0) {
            return 0.0;
        }
        $paidSessions = $this->collected_amount / $sessionCost;
        return min((float) $this->total_sessions, round($paidSessions, 1));
    }

    /**
     * عدد الجلسات المتبقية التي لم يتم تحصيل قيمتها بعد
     */
    public function getRemainingSessionsToCollectAttribute(): float
    {
        return max(0.0, round($this->total_sessions - $this->paid_sessions_count, 1));
    }

    /**
     * مقارنة الجلسات المحصلة بالجلسات المنفذة فعلياً
     */
    public function getAttendedVsPaidDifferenceAttribute(): float
    {
        return round($this->paid_sessions_count - $this->completed_sessions_count, 1);
    }

    /**
     * وصف الحالة المالية لتنفيذ الجلسات
     */
    public function getAttendanceFinancialStatusAttribute(): array
    {
        $diff = $this->attended_vs_paid_difference;
        if ($diff > 0) {
            return [
                'type' => 'advance',
                'label' => 'مدفوع مقدماً (' . $diff . ' جلسة)',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ];
        } elseif ($diff < 0) {
            return [
                'type' => 'due',
                'label' => 'مستحق سداد (' . abs($diff) . ' جلسة)',
                'badge_class' => 'bg-rose-100 text-rose-800 border-rose-200',
            ];
        }

        return [
            'type' => 'balanced',
            'label' => 'المدفوع مطابق للمنفذ',
            'badge_class' => 'bg-blue-100 text-blue-800 border-blue-200',
        ];
    }
}
