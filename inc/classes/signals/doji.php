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

        // As dojis need to be relatively small in size, the current period candle must be less than the period average
        $candle_size = abs($last_period->high - $last_period->low);
        $avg_candle_size = ((get::averageCandleSize($data, self::CANDLE_AVERAGE_PERIOD) / 3) * 2); // 2/3 of the AVG

        if ($candle_size <= $avg_candle_size) {
            // Let's make sure our close price was somewhere within the middle 33% of the candle - This shows indecisive price movement
            $min_close = ((33.33 * ($last_period->high - $last_period->low) / 100) + $last_period->low);
            $max_close = ((66.66 * ($last_period->high - $last_period->low) / 100) + $last_period->low);

            if ($last_period->close >= $min_close && $last_period->close <= $max_close) {
                $body_size = abs($last_period->open - $last_period->close);

                $body_percentage = (($body_size / $candle_size) * 100);
                if ($body_percentage <= 25) {
                    return true;
                }
            }
        }

        return false;
    }
}