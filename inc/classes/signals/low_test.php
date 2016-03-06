<?php
class low_test extends _signal {

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function isValidSignal(array $data): bool {
        $last_period = end($data);

        /**@var avg_price_data $last_period*/
        $body_bottom = ($last_period->close < $last_period->open ? $last_period->close : $last_period->open);

        $candle_top = abs($last_period->high - $body_bottom);
        $candle_bottom = abs($body_bottom - $last_period->low);

        if ($candle_bottom >= ($candle_top * 2)) {
            return true;
        }

        return false;
    }
}