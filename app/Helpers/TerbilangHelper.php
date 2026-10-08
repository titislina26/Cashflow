<?php

namespace App\Helpers;

class TerbilangHelper
{
    /**
     * Convert an integer number to Indonesian spelling words.
     *
     * @param int|float $number
     * @return string
     */
    public static function convert($number): string
    {
        $number = (int) abs($number);
        $words = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $temp = "";

        if ($number < 12) {
            $temp = " " . $words[$number];
        } elseif ($number < 20) {
            $temp = self::convert($number - 10) . " belas";
        } elseif ($number < 100) {
            $temp = self::convert((int)($number / 10)) . " puluh" . self::convert($number % 10);
        } elseif ($number < 200) {
            $temp = " seratus" . self::convert($number - 100);
        } elseif ($number < 1000) {
            $temp = self::convert((int)($number / 100)) . " ratus" . self::convert($number % 100);
        } elseif ($number < 2000) {
            $temp = " seribu" . self::convert($number - 1000);
        } elseif ($number < 1000000) {
            $temp = self::convert((int)($number / 1000)) . " ribu" . self::convert($number % 1000);
        } elseif ($number < 1000000000) {
            $temp = self::convert((int)($number / 1000000)) . " juta" . self::convert($number % 1000000);
        } elseif ($number < 1000000000000) {
            $temp = self::convert((int)($number / 1000000000)) . " miliar" . self::convert($number % 1000000000);
        } elseif ($number < 1000000000000000) {
            $temp = self::convert((int)($number / 1000000000000)) . " triliun" . self::convert($number % 1000000000000);
        }

        return $temp;
    }

    /**
     * Get the full spelling in Indonesian with "Rupiah" suffix, properly capitalized.
     *
     * @param int|float $number
     * @return string
     */
    public static function spelling($number): string
    {
        $num = (int) $number;
        if ($num === 0) {
            return "Nol Rupiah";
        }
        $spelled = preg_replace('/\s+/', ' ', trim(self::convert($num)));
        return ucwords($spelled) . " Rupiah";
    }
}
