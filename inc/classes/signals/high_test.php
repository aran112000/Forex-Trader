<?php

/**
 * Class high_test
 */
class high_test extends _signal {

    /**
     * @param array  $data
     * @param string $direction
     *
     * @return bool
     */
    public static function isValidSignal(array $data, string $direction): bool {
        if ($direction === 'long') {
            trigger_error('Error: High test called for a short entry');
            return false;
        }

        /**@var avg_price_data $last_period */
        $last_period = end($data);

        $body_top = ($last_period->close > $last_period->open ? $last_period->close : $last_period->open);

        $candle_top = abs($last_period->high - $body_top);
        $candle_bottom = abs($body_top - $last_period->low);

        if ($candle_top >= ($candle_bottom * 2)) {
            return true;
        }

        return false;
    }
}