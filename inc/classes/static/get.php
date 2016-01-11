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
        $difference = abs($a - $b);

        return ($difference * 10000);
    }
}