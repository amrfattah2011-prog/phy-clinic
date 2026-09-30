<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TherapySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'therapy_plan_id',
        'patient_id',
        'session_number',
        'session_date',
        'is_attended',
        'attended_at',
        'attended_by_user_id',
        'notes',
    ];

    public function attendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_by_user_id');
    }

    protected $casts = [
        'session_date' => 'date',
        'is_attended' => 'boolean',
        'attended_at' => 'datetime',
        'session_number' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TherapyPlan::class, 'therapy_plan_id');
    }

    public function therapyPlan(): BelongsTo
    {
        return $this->plan();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(ClinicDevice::class, 'session_devices', 'therapy_session_id', 'clinic_device_id')->withTimestamps();
    }
}
