<?php

/**
 * Class low_test
 */
class low_test extends _signal {

    /**
     * @param array  $data
     * @param string $direction
     *
     * @return bool
     */
    public static function isValidSignal(array $data, string $direction): bool {
        if ($direction === 'short') {
            trigger_error('Error: High test called for a short entry');

            return false;
        }

        /**@var avg_price_data $last_period*/
        $last_period = end($data);
        $last_direction = $last_period->getDirection();

        if ($direction === 'long' && ($last_direction === 'up' || $last_period === 'neutral')) {
            $body_bottom = ($last_period->close < $last_period->open ? $last_period->close : $last_period->open);

            $candle_top = abs($last_period->high - $body_bottom);
            $candle_bottom = abs($body_bottom - $last_period->low);

            if ($candle_bottom >= ($candle_top * 2)) {
                return true;
            }
        }

        return false;
    }
}