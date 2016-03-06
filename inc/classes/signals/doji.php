<?php

/**
 * Class doji
 */
class doji extends _signal {

    const CANDLE_AVERAGE_PERIOD = 15; // Periods

    const MAX_BODY_PIPS = 5; // The maximum pips the body of the candle can be of the full day
    const ACCEPTED_MAX_BODY_PERCENTAGE = 30; // The percentage the body of the candle can be of the full day

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function isValidSignal(array $data): bool {
        /**@var avg_price_data $last_period*/
        $last_period = end($data);
        $avg_candle_size = self::getAverageCandleSize($data);

        // As dojis need to be relatively small in size, the current period candle must be less than the period average
        $last_candle_size = abs($last_period->high - $last_period->low);
        $last_candle_body_size = abs($last_period->open - $last_period->close);

        if ($last_candle_size < $avg_candle_size) {
            $body_pip_size = get::pip_difference($last_period->open, $last_period->close, $last_period->pair);
            $body_percentage_size = (($last_candle_body_size / $last_candle_size) * 100);

            if ($body_pip_size <= self::MAX_BODY_PIPS && $body_percentage_size <= self::ACCEPTED_MAX_BODY_PERCENTAGE) {
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

        return ($sum / self::CANDLE_AVERAGE_PERIOD);
    }
}