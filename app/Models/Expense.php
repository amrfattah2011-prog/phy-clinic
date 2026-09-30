<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'amount',
        'expense_date',
        'payment_method',
        'receipt_reference',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public static function categories(): array
    {
        return [
            'rent' => 'إيجار المركز',
            'utilities' => 'كهرباء ومياه وإنترنت',
            'medical_supplies' => 'مستلزمات طبية وأدوية',
            'maintenance' => 'صيانة أجهزة ومعدات',
            'salaries' => 'مرتبات ومكافآت العاملين',
            'hospitality' => 'ضيافة ونظافة ومطبوعات',
            'marketing' => 'تسويق وإعلانات',
            'other' => 'مصاريف إدارية ونثرية أخرى',
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            'cash' => 'نقداً (خزينة المركز)',
            'visa' => 'فيزا / تحويل بنكي',
            'vodafone_cash' => 'فودافون كاش / محافظ إلكترونية',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getCategoryNameAttribute(): string
    {
        return self::categories()[$this->category] ?? 'أخرى';
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return self::paymentMethods()[$this->payment_method] ?? 'نقداً';
    }
}
