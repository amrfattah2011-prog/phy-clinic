<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClinicDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function therapySessions(): BelongsToMany
    {
        return $this->belongsToMany(TherapySession::class, 'session_devices')->withTimestamps();
    }

    public function weightLossSessions(): BelongsToMany
    {
        return $this->belongsToMany(WeightLossSession::class, 'session_devices')->withTimestamps();
    }

    public function getTypeArabicAttribute(): string
    {
        return match ($this->type) {
            'therapy' => 'علاج طبيعي',
            'weight_loss' => 'تخسيس ونحت قوام',
            'both' => 'علاج طبيعي وتخسيس',
            default => $this->type,
        };
    }
}
