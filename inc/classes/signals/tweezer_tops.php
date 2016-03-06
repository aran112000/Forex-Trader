<?php
class tweezer_tops extends _signal {

    const ALLOWED_PERCENTAGE_DIFFERENCE = 10; // The %age +/- the candles can differ in size

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function isValidSignal(array $data): bool {
        $last_two_periods = array_slice($data, -2);

        if (count($last_two_periods) == 2) {
            /**@var avg_price_data $first_period , $last_period */
            $first_period = $last_two_periods[0];
            $last_period = $last_two_periods[1];

            $first_size = abs($first_period->open - $first_period->close);
            $last_size = abs($last_period->open - $last_period->close);

            if ($last_size == 0 || $first_size == 0) {
                return false;
            }

            $percentage_difference = abs(($first_size / $last_size) * 100);

            // Check the last two candles are similar enough in size
            if ($percentage_difference <= self::ALLOWED_PERCENTAGE_DIFFERENCE) {
                $first_middle = ($first_period->close > $first_period->open ? $first_period->close : $first_period->open);
                $first_top_size = abs($first_period->high - $first_middle);
                $first_bottom_size = abs($first_middle - $first_period->low);

                $last_middle = ($last_period->close > $last_period->open ? $last_period->close : $last_period->open);
                $last_top_size = abs($last_period->high - $last_middle);
                $last_bottom_size = abs($last_middle - $last_period->low);

                if ($first_top_size >= $first_bottom_size && $last_top_size >= $last_bottom_size) {
                    return true;
                }
            }
        }

        return false;
    }
}