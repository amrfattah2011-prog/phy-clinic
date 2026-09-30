<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'gender',
        'age',
        'medical_history',
        'portal_token',
        'created_by_user_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (empty($patient->portal_token)) {
                $patient->portal_token = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(16));
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->orderBy('visit_date', 'desc');
    }

    public function therapyPlans(): HasMany
    {
        return $this->hasMany(TherapyPlan::class)->orderBy('created_at', 'desc');
    }

    public function therapySessions(): HasMany
    {
        return $this->hasMany(TherapySession::class)->orderBy('session_date', 'asc');
    }

    public function weightLossPlans(): HasMany
    {
        return $this->hasMany(WeightLossPlan::class)->orderBy('created_at', 'desc');
    }

    public function weightLossSessions(): HasMany
    {
        return $this->hasMany(WeightLossSession::class)->orderBy('session_date', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc')->orderBy('id', 'desc');
    }

    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class, 'patient_disease')->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PatientAttachment::class)->orderBy('document_date', 'desc')->orderBy('id', 'desc');
    }

    public function getTotalCostAttribute(): float
    {
        $visitsTotal = (float) $this->visits()->sum('cost');
        $therapyPlansTotal = (float) $this->therapyPlans()->sum('cost');
        $weightLossPlansTotal = (float) $this->weightLossPlans()->sum('cost');
        // Individual sessions not linked to a package
        $standaloneWeightLossTotal = (float) $this->weightLossSessions()->whereNull('weight_loss_plan_id')->sum('cost');

        return $visitsTotal + $therapyPlansTotal + $weightLossPlansTotal + $standaloneWeightLossTotal;
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, $this->total_cost - $this->total_paid);
    }

    public function getGenderArabicAttribute(): string
    {
        return $this->gender === 'male' ? 'ذكر' : 'أنثى';
    }

    public function getWhatsappPhoneAttribute(): string
    {
        $raw = preg_replace('/[^0-9]/', '', (string) $this->phone);

        if (empty($raw)) {
            return '';
        }

        // If starts with 00, strip the 00
        if (str_starts_with($raw, '00')) {
            $raw = substr($raw, 2);
        }

        // Egyptian local mobile numbers: 11 digits starting with 01 (e.g. 010, 011, 012, 015)
        if (str_starts_with($raw, '01') && strlen($raw) === 11) {
            return '2' . $raw;
        }

        // Gulf / Saudi local mobile: 10 digits starting with 05
        if (str_starts_with($raw, '05') && strlen($raw) === 10) {
            return '966' . substr($raw, 1);
        }

        // Other local numbers starting with 0
        if (str_starts_with($raw, '0') && strlen($raw) >= 10) {
            return '2' . $raw;
        }

        return $raw;
    }

    public function getWhatsappLinkAttribute(): string
    {
        $phone = $this->whatsapp_phone;
        return $phone ? "https://wa.me/{$phone}" : '#';
    }

    public function ensurePortalToken(): string
    {
        if (empty($this->portal_token)) {
            $this->portal_token = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(16));
            $this->saveQuietly();
        }
        return $this->portal_token;
    }

    public function getPortalUrlAttribute(): string
    {
        $token = $this->portal_token ?: $this->ensurePortalToken();
        return url("/p/{$token}");
    }
}
