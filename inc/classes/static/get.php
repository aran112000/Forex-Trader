<?php

/**
 * Class get
 */
final class get {

    /**
     * @param array $data
     * @param int   $history_periods
     *
     * @return bool|string
     */
    public static function historicalPriceDirection(array $data, $history_periods = 5) {
        $data = array_slice($data, -$history_periods);

        if (count($data) === $history_periods) {
            /**@var avg_price_data $start*/
            $start = array_shift($data);
            $last_price = $start->close;
            $difference = 0;

            foreach ($data as $row) {
                /**@var avg_price_data $row*/
                $difference += self::pipDifference($last_price, $row->close, $row->pair, false);
                $last_price = $row->close;
            }

            if ($difference > 0) {
                return 'up';
            } else if ($difference < 0) {
                return 'down';
            } else {
                return 'neutral';
            }
        }

        return false;
    }

    /**
     * @param string $date_string
     * @param string $uk_date_string
     *
     * @return string
     */
    public static function date(string $date_string, string $uk_date_string): string {
        list($day, $month, $year) = explode('/', $uk_date_string);

        return date($date_string, strtotime($month . '/' . $day . '/' . $year));
    }

    /**
     * @param array $full_data
     * @param int   $average_period
     *
     * @return float
     */
    public static function averageCandleSize(array $full_data, int $average_period = 15): float {
        $period_data = array_slice($full_data, -$average_period);

        $sum = 0;
        foreach ($period_data as $candle) {
            /**@var avg_price_data $candle */
            $candle_size = abs($candle->high - $candle->low);

            $sum += $candle_size;
        }

        return ($sum / count($period_data));
    }

    /**
     * @param float  $a
     * @param float  $b
     * @param \_pair $pair
     * @param bool   $abs
     *
     * @return float
     */
    public static function pipDifference(float $a, float $b, _pair $pair, $abs = true): float {
        $multiplier = 10000; // For currency pairs displayed to four decimal places, one pip is equal to 0.0001
        if ($pair->base_currency === 'JPY' || $pair->quote_currency === 'JPY') {
            // Yen-based currency pairs are an exception and are displayed to only two decimal places (0.01)
            $multiplier = 100;
        }

        if ($abs) {
            return (abs($a - $b) * $multiplier);
        } else {
            return (($a - $b) * $multiplier);
        }
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