<?php

/**
 * Class get
 */
final class get {

    /**
     * @param float $a
     * @param float $b
     *
     * @return float
     */
    public static function pip_difference(float $a, float $b): float {
        return (abs($a - $b) * 10000);
    }


    /**
     * @param string $string
     *
     * @return int|float|string
     */
    public static function int_float_from_string($string) {
        if (is_numeric($string)) {
            $float_val = floatval($string);
            if ($float_val == (int) $float_val) {
                return (int) $float_val;
            } else {
                return (float) $float_val;
            }
        }

        return $string;
    }
}