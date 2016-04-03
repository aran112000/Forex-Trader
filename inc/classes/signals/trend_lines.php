<?php

/**
 * Class trend_lines
 */
class trend_lines {

    const BOUNCE_AVG_PERIOD = 1;

    /**
     * @var array
     */
    private static $trend_confluence = [];

    private static $bounces = [];

    /**
     * @param array $data
     *
     * @return array
     */
    public static function getLines(array $data): array {
        self::setBounces($data);

        return self::$bounces;
    }

    /**
     * @param array $data
     */
    private static function setBounces(array $data) {
        $number_of_data_points = ((self::BOUNCE_AVG_PERIOD * 2) + 1); // Middle day, + avg. period either side
        $points_of_interest = array_slice($data, -$number_of_data_points);

        /**@var avg_price_data $current_day*/
        $current_day = $points_of_interest[self::BOUNCE_AVG_PERIOD];
        $pre_data = array_slice($points_of_interest, 0, (self::BOUNCE_AVG_PERIOD + 1));
        $post_data = array_slice($points_of_interest, self::BOUNCE_AVG_PERIOD, (self::BOUNCE_AVG_PERIOD + 1));

        $pre_direction = get::historicalPriceDirection($pre_data, (self::BOUNCE_AVG_PERIOD + 1));
        $post_direction = get::historicalPriceDirection($post_data, (self::BOUNCE_AVG_PERIOD + 1));

        if ($pre_direction !== $post_direction) {
            $confluence_point = self::getConfluencePoint($current_day->close, $current_day->getDirection(), $current_day->pair);
            if (!isset(self::$bounces[$confluence_point])) {
                self::$bounces[$confluence_point] = 0;
            }

            self::$bounces[$confluence_point]++;

            arsort(self::$bounces, SORT_NUMERIC);
        }

        if (count($data) >= 260) {
            $a = 1+1; // TODO; Remove - Only here to allow a breakpoint to be added easily
        }
    }

    /**
     * @param float  $close_price
     * @param string $direction
     * @param \_pair $pair
     *
     * @return bool|string
     */
    private static function getConfluencePoint(float $close_price, string $direction, _pair $pair) {
        $decimal_places = 5;
        if ($pair->base_currency === 'JPY' || $pair->quote_currency === 'JPY') {
            $decimal_places = 3;
        }

        $multiplier = pow(10, ($decimal_places - 2));
        if ($direction === 'up') {
            $round = floor($close_price * $multiplier);
        } else if ($direction === 'down') {
            $round = ceil($close_price * $multiplier);
        } else {
            $round = round($close_price * $multiplier);
        }

        return number_format($round / $multiplier, 5, '.', '');
    }
}