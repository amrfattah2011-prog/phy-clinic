<?php

namespace App\Helpers;

class Tafqeet
{
    private static array $ones = [
        0 => '', 1 => 'واحد', 2 => 'اثنان', 3 => 'ثلاثة', 4 => 'أربعة',
        5 => 'خمسة', 6 => 'ستة', 7 => 'سبعة', 8 => 'ثمانية', 9 => 'تسعة',
        10 => 'عشرة', 11 => 'أحد عشر', 12 => 'اثنا عشر', 13 => 'ثلاثة عشر',
        14 => 'أربعة عشر', 15 => 'خمسة عشر', 16 => 'ستة عشر', 17 => 'سبعة عشر',
        18 => 'ثمانية عشر', 19 => 'تسعة عشر'
    ];

    private static array $tens = [
        2 => 'عشرون', 3 => 'ثلاثون', 4 => 'أربعون', 5 => 'خمسون',
        6 => 'ستون', 7 => 'سبعون', 8 => 'ثمانون', 9 => 'تسعون'
    ];

    private static array $hundreds = [
        0 => '', 1 => 'مائة', 2 => 'مائتان', 3 => 'ثلاثمائة', 4 => 'أربعمائة',
        5 => 'خمسمائة', 6 => 'ستمائة', 7 => 'سبعمائة', 8 => 'ثمانمائة', 9 => 'تسعمائة'
    ];

    public static function inArabic(float|int $number, string $currency = 'جنيه مصري'): string
    {
        if ($number == 0) {
            return 'صفر ' . $currency;
        }

        $integerPart = (int) floor($number);
        $fractionPart = (int) round(($number - $integerPart) * 100);

        $text = self::convertNumber($integerPart);
        $result = 'فقط ' . $text . ' ' . $currency;

        if ($fractionPart > 0) {
            $result .= ' و ' . self::convertNumber($fractionPart) . ' قرشاً';
        }

        return $result . ' لا غير';
    }

    private static function convertNumber(int $num): string
    {
        if ($num === 0) return '';
        if ($num < 20) return self::$ones[$num];
        if ($num < 100) {
            $one = $num % 10;
            $ten = (int) floor($num / 10);
            return ($one > 0 ? self::$ones[$one] . ' و ' : '') . self::$tens[$ten];
        }
        if ($num < 1000) {
            $hundred = (int) floor($num / 100);
            $rest = $num % 100;
            return self::$hundreds[$hundred] . ($rest > 0 ? ' و ' . self::convertNumber($rest) : '');
        }
        if ($num < 1000000) {
            $thousands = (int) floor($num / 1000);
            $rest = $num % 1000;
            $thWord = match ($thousands) {
                1 => 'ألف',
                2 => 'ألفان',
                3, 4, 5, 6, 7, 8, 9, 10 => self::convertNumber($thousands) . ' آلاف',
                default => self::convertNumber($thousands) . ' ألفاً',
            };
            return $thWord . ($rest > 0 ? ' و ' . self::convertNumber($rest) : '');
        }

        return (string) $num;
    }
}
