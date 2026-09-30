<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_name',
        'doctor_name',
        'doctor_title',
        'phone_1',
        'phone_2',
        'address',
        'license_number',
        'receipt_footer',
        'currency',
    ];

    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'clinic_name' => 'عيادة د. أحمد عادل',
            'doctor_name' => 'د. أحمد عادل',
            'doctor_title' => 'استشاري العلاج الطبيعي وتأهيل الإصابات وتنسيق القوام',
            'phone_1' => '01000000000',
            'phone_2' => '01200000000',
            'address' => 'شارع الجمهورية - برج الأطباء - الدور الثالث',
            'license_number' => '4892 / ع.ط',
            'receipt_footer' => 'تنبيه: يرجى إحضار الإيصال في كل زيارة للمتابعة • نتمنى لكم دوام الصحة والعافية',
            'currency' => 'ج.م',
        ]);
    }
}
