<?php

/**
 * Class doji
 */
class doji extends _signal {

    const CANDLE_AVERAGE_PERIOD = 15; // Periods

    /**
     * @param array  $data
     * @param string $direction
     *
     * @return bool
     */
    public static function isValidSignal(array $data, string $direction): bool {
        /**@var avg_price_data $last_period*/
        $last_period = end($data);
        $avg_candle_size = self::getAverageCandleSize($data);

        // As dojis need to be relatively small in size, the current period candle must be less than the period average
        $last_candle_size = abs($last_period->high - $last_period->low);

        if ($last_candle_size <= $avg_candle_size) {
            $body_size = abs($last_period->open - $last_period->close);
            $top_size = abs($last_period->high - ($last_period->open > $last_period->close ? $last_period->open : $last_period->close));
            $bottom_size = abs($last_period->low - ($last_period->open < $last_period->close ? $last_period->open : $last_period->close));

            if ($body_size <= $top_size && $body_size <= $bottom_size) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $full_data
     *
     * @return float
     */
    private static function getAverageCandleSize(array $full_data): float {
        $period_data = array_slice($full_data, (0 - self::CANDLE_AVERAGE_PERIOD));

        $sum = 0;
        foreach ($period_data as $candle) {
            /**@var avg_price_data $candle*/
            $candle_size = abs($candle->high - $candle->low);

            $sum += $candle_size;
        }

        return ($sum / count($period_data));
    }
}