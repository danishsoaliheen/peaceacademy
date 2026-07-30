<?php

namespace App\Helpers;

class NumberToWords
{
    /*
    |--------------------------------------------------------------------------
    | Convert a number to English words (Pakistani Rupee context)
    |--------------------------------------------------------------------------
    | Usage: NumberToWords::convert(5500)  =>  "Five Thousand Five Hundred"
    |        NumberToWords::convert(1500.50) =>  "One Thousand Five Hundred And Fifty Paise"
    |--------------------------------------------------------------------------
    */

    private static array $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven',
        'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen',
        'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen',
        'Nineteen',
    ];

    private static array $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
        'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    public static function convert($number): string
    {
        if ($number == 0) {
            return 'Zero';
        }

        $number = (float) $number;

        // Split into whole and decimal parts
        $whole  = (int) floor($number);
        $paise  = round(($number - $whole) * 100);

        $words = self::convertWhole($whole);

        if ($paise > 0) {
            $words .= ' And ' . self::convertWhole($paise) . ' Paise';
        }

        return trim($words);
    }

    private static function convertWhole(int $num): string
    {
        if ($num < 20) {
            return self::$ones[$num];
        }

        if ($num < 100) {
            $ten = (int) floor($num / 10);
            $one = $num % 10;
            return self::$tens[$ten] . ($one ? ' ' . self::$ones[$one] : '');
        }

        if ($num < 1000) {
            $hundred = (int) floor($num / 100);
            $rest    = $num % 100;
            return self::$ones[$hundred] . ' Hundred' . ($rest ? ' And ' . self::convertWhole($rest) : '');
        }

        // Scale: Thousand, Lakh, Crore (Pakistani/Indian numbering)
        $scales = [
            10000000 => 'Crore',
            100000   => 'Lakh',
            1000     => 'Thousand',
        ];

        foreach ($scales as $scale => $label) {
            if ($num >= $scale) {
                $part = (int) floor($num / $scale);
                $rest = $num % $scale;
                return self::convertWhole($part) . ' ' . $label . ($rest ? ' ' . self::convertWhole($rest) : '');
            }
        }

        return (string) $num;
    }
}