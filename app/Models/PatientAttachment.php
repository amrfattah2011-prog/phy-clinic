<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PatientAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'title',
        'category',
        'document_date',
        'file_path',
        'original_name',
        'file_type',
        'mime_type',
        'file_size',
        'original_size',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'document_date' => 'date',
        'file_size'     => 'integer',
        'original_size' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->file_type === 'pdf' || str_contains(strtolower($this->mime_type), 'pdf');
    }

    public function getIsImageAttribute(): bool
    {
        return $this->file_type === 'image' || str_starts_with(strtolower($this->mime_type), 'image/');
    }

    public static function categories(): array
    {
        return [
            'xray'         => 'أشعة وفحوصات تصويرية 🩻',
            'lab'          => 'تحاليل مخبرية 🧪',
            'report'       => 'تقرير طبي واستشاري 📄',
            'photo'        => 'صور الحالة ومتابعة القوام 📷',
            'prescription' => 'روشتة علاجية سابقة 💊',
            'general'      => 'مستندات وملفات عامة 📁',
        ];
    }

    public function getCategoryNameAttribute(): string
    {
        $cats = static::categories();
        return $cats[$this->category] ?? 'مستند طبي 📁';
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' ميجابايت';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' كيلوبايت';
        }
        return $bytes . ' بايت';
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('patients.attachments.download', ['patient' => $this->patient_id, 'attachment' => $this->id]);
    }

    public function getViewUrlAttribute(): string
    {
        return route('patients.attachments.view', ['patient' => $this->patient_id, 'attachment' => $this->id]);
    }

    /**
     * نسبة التوفير في المساحة بعد الضغط
     */
    public function getCompressionSavingsPercentageAttribute(): ?float
    {
        if ($this->original_size && $this->original_size > $this->file_size) {
            return round((($this->original_size - $this->file_size) / $this->original_size) * 100, 1);
        }
        return null;
    }

    /**
     * حجم الملف الأصلي قبل الضغط
     */
    public function getFormattedOriginalSizeAttribute(): ?string
    {
        if (!$this->original_size) {
            return null;
        }
        $bytes = $this->original_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' ميجابايت';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' كيلوبايت';
        }
        return $bytes . ' بايت';
    }
}