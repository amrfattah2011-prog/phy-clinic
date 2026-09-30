<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class WeightLossSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'weight_loss_plan_id',
        'session_number',
        'session_date',
        'weight',
        'height',
        'bmi',
        'dosage_amount',
        'injection_type',
        'doctor_notes',
        'cost',
        'is_completed',
        'completed_at',
        'completed_by_user_id',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_mime',
        'attachment_size',
        'original_size',
        'compression_ratio',
    ];

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    protected $casts = [
        'session_date' => 'date',
        'session_number' => 'integer',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'attachment_size' => 'integer',
        'original_size' => 'integer',
        'compression_ratio' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->weight && $model->height && $model->height > 0) {
                $heightInMeters = $model->height / 100;
                $model->bmi = round($model->weight / ($heightInMeters * $heightInMeters), 2);
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WeightLossPlan::class, 'weight_loss_plan_id');
    }

    public function weightLossPlan(): BelongsTo
    {
        return $this->plan();
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(ClinicDevice::class, 'session_devices', 'weight_loss_session_id', 'clinic_device_id')->withTimestamps();
    }

    public function getBmiCategoryAttribute(): string
    {
        if (!$this->bmi) {
            return '-';
        }
        if ($this->bmi < 18.5) return 'نقص في الوزن';
        if ($this->bmi < 25) return 'وزن مثالي';
        if ($this->bmi < 30) return 'زيادة في الوزن';
        if ($this->bmi < 35) return 'سمنة درجة أولى';
        if ($this->bmi < 40) return 'سمنة درجة ثانية';
        return 'سمنة مفرطة خطيرة';
    }

    public function getHasAttachmentAttribute(): bool
    {
        return !empty($this->attachment_path);
    }

    public function getIsAttachmentPdfAttribute(): bool
    {
        return $this->attachment_type === 'pdf' || str_contains(strtolower($this->attachment_mime ?? ''), 'pdf');
    }

    public function getIsAttachmentImageAttribute(): bool
    {
        return $this->attachment_type === 'image' || str_starts_with(strtolower($this->attachment_mime ?? ''), 'image/');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }
        return Storage::disk('public')->url($this->attachment_path);
    }

    public function getViewUrlAttribute(): ?string
    {
        if (!$this->has_attachment) {
            return null;
        }
        return route('weight-loss-sessions.attachment.view', $this);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (!$this->has_attachment) {
            return null;
        }
        return route('weight-loss-sessions.attachment.download', $this);
    }

    public function getFormattedAttachmentSizeAttribute(): string
    {
        if (!$this->attachment_size) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->attachment_size;
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getFormattedOriginalSizeAttribute(): string
    {
        if (!$this->original_size) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->original_size;
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getCompressionSavingsPercentageAttribute(): ?int
    {
        if ($this->original_size && $this->attachment_size && $this->original_size > $this->attachment_size) {
            $ratio = (1 - ($this->attachment_size / $this->original_size)) * 100;
            return (int) round($ratio);
        }
        return $this->compression_ratio ? (int) round($this->compression_ratio) : null;
    }
}
