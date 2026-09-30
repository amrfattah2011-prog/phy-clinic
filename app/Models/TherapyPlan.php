<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TherapyPlan extends Model
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
        return $this->hasMany(TherapySession::class)->orderBy('session_number', 'asc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getCompletedSessionsCountAttribute(): int
    {
        return $this->sessions()->where('is_attended', true)->count();
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
}
