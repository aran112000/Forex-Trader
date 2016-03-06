<?php

class high_test extends _signal {

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function isValidSignal(array $data): bool {
        $last_period = end($data);

        /**@var avg_price_data $last_period */
        $body_top = ($last_period->close > $last_period->open ? $last_period->close : $last_period->open);

        $candle_top = abs($last_period->high - $body_top);
        $candle_bottom = abs($body_top - $last_period->low);

        if ($candle_top >= ($candle_bottom * 2)) {
            return true;
        }

        return false;
    }
}