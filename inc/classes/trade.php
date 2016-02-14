<?php

/**
 * Class trade
 */
final class trade {

    const MAXIMUM_PERCENTAGE_RISK = 1;
    CONST SECONDS_TO_LEAVE_AN_ORDER_BEFORE_EXPIRY = 30;

    /**
     * @var \_pair|null
     */
    private $pair = null;

    /**
     * @var null|\oanda_rest_api
     */
    private $oanda_api = null;

    /**
     * trade constructor.
     *
     * @param \_pair $pair
     */
    public function __construct(_pair $pair) {
        $this->pair = $pair;
        $this->oanda_api = new oanda_rest_api();
    }

    /**
     * @param float $buy_price
     * @param float $stop_price
     *
     * @return bool
     */
    public function doBuy(float $buy_price, float $stop_price): bool {
        if ($buy_price <= $stop_price) {
            throw new InvalidArgumentException('Prices are too close together. Buy: ' . $buy_price . ' Stop: ' . $stop_price);
        }

        $this->oanda_api->doApiRequest('accounts/' . oanda_base::ACCOUNT_ID . '/orders', [
            'instrument' => $this->pair->getPairName(),
            'units' => $this->getNumberUnitsToTrade($buy_price, $stop_price),
            'side' => 'buy',
            'type' => 'stop',
            'expiry' => $this->getExpiaryDateTime(),
            'price' => $buy_price,
            'trailingStop' => $this->getTrailingStop($buy_price, $stop_price), // The trailing stop distance in pips, up to one decimal place
        ]);
    }

    /**
     * @param float $sell_price
     * @param float $stop_price
     *
     * @return bool
     */
    public function doSell(float $sell_price, float $stop_price): bool {
        $this->oanda_api->doApiRequest('accounts/' . oanda_base::ACCOUNT_ID . '/orders', [
            'instrument' => $this->pair->getPairName(),
            'units' => $this->getNumberUnitsToTrade($sell_price, $stop_price),
            'side' => 'sell',
            'type' => 'stop',
            'expiry' => $this->getExpiaryDateTime(),
            'price' => $sell_price,
            'trailingStop' => $this->getTrailingStop($sell_price, $stop_price), // The trailing stop distance in pips, up to $sell_price decimal place
        ]);
    }

    /**
     * @param float $entry_rate
     * @param float $exit_rate
     *
     * @return float
     */
    private function getTrailingStop(float $entry_rate, float $exit_rate): float {
        $pip_difference = (abs($entry_rate - $exit_rate) * 10000);

        return round(($pip_difference / 5), 1, PHP_ROUND_HALF_DOWN); // This will give us a trailing stop set to 1/5 of the total position size
    }

    /**
     * @param float $entry_rate
     * @param float $exit_rate
     *
     * @return float
     */
    private function getNumberUnitsToTrade(float $entry_rate, float $exit_rate): float {
        return ((account::getBalance() * (self::MAXIMUM_PERCENTAGE_RISK / 100)) / abs($entry_rate - $exit_rate));
    }

    /**
     * @param int $unix_timestamp
     *
     * @return int
     */
    private function getUtcUnixTimestamp(int $unix_timestamp): int {
        return strtotime(gmdate('d/m/Y H:i:s', $unix_timestamp));
    }

    /**
     * @return int
     */
    private function getExpiaryDateTime(): int {
        return $this->getUtcUnixTimestamp(strtotime('+' . self::SECONDS_TO_LEAVE_AN_ORDER_BEFORE_EXPIRY . ' seconds'));
    }
}