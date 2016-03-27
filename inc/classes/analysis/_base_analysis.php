<?php

/**
 * Class _base_analysis
 */
abstract class _base_analysis {

    private $last_run_date = null;

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = null;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var bool
     */
    private $testing = false;

    /**
     * @var _pair|null
     */
    protected $currency_pair = null;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int
     */
    protected $data_fetch_size = 50;

    /**
     * @return array
     */
    public function doAnalyse(): array {
        $method = get_called_class();

        log::write($method . ' called', log::INFO);
        $trade_details = [];

        // This is ONLY enabled once each day just after 22:00 (a couple of minutes allows for any time delays)
        if ((defined('testing') && testing) || (gmdate('H:i') === '22:05' && $this->last_run_date !== gmdate('d/m/Y'))) {
            log::write('Checking for ' . $method . ' as time = 22:05', log::DEBUG);

            $this->last_run_date = gmdate('d/m/Y');

            if ($this->isShortEntry()) {
                $direction = 'short';
                $trade_details = $this->getTradeDetails($direction);

                if (!defined('testing') || !testing) {
                    if ($direction !== false) {
                        log::write($direction . ' trade found', log::DEBUG);
                        email::send($method . ' entry signal found', '<p>A ' . $direction . ' entry opportunity for ' . $this->currency_pair->getPairName('/') . '</p><p><pre>' . print_r($trade_details, true) . '</pre></p>', 'cdtreeks@gmail.com,jainikadrenkhan@gmail.com');
                    } else {
                        log::write('No trade found', log::DEBUG);
                    }
                }
            }

            if ($this->isLongEntry()) {
                $direction = 'long';
                $trade_details = $this->getTradeDetails($direction);

                if (!defined('testing') || !testing) {
                    if ($direction !== false) {
                        log::write($direction . ' trade found', log::DEBUG);
                        email::send($method . ' entry signal found', '<p>A ' . $direction . ' entry opportunity for ' . $this->currency_pair->getPairName('/') . '</p><p><pre>' . print_r($trade_details, true) . '</pre></p>', 'cdtreeks@gmail.com,jainikadrenkhan@gmail.com');
                    } else {
                        log::write('No trade found', log::DEBUG);
                    }
                }
            }
        }

        return $trade_details;
    }

    /**
     * @param string $direction
     *
     * @return mixed
     */
    protected function getTradeDetails(string $direction): array {
        $data = $this->getData();

        /**@var avg_price_data $latest_day */
        $latest_day = end($data);

        if ($direction === 'long') {
            $type = 'Buy';
            $entry = $latest_day->high + 0.0002;
            $stop = $latest_day->low - 0.0002;

            if ($entry <= $stop) {
                return [];
            }
        } else {
            $type = 'Sell';
            $entry = $latest_day->low - 0.0002;
            $stop = $latest_day->high + 0.0002;

            if ($entry >= $stop) {
                return [];
            }
        }

        $pip_difference = get::pipDifference($entry, $stop, $latest_day->pair);
        $balance = account::getBalance();

        $max_per_pip = (($balance / 100) / $pip_difference);
        $amount = (($balance / 100) / ($max_per_pip + $latest_day->spread));

        return [
            'type' => $type,
            'start_date_time' => $latest_day->start_date_time,
            'pair' => $this->currency_pair,
            'entry' => $entry,
            'stop' => $stop,
            'current_balance' => $balance,
            'pip_difference' => $pip_difference,
            'max_per_pip' => round($max_per_pip, 3),
            'amount' => $amount,
        ];
    }

    /**
     * @param array $ema_periods
     *
     * @return mixed
     */
    protected function getEmas(array $ema_periods): array {
        $data = $this->getData();
        $data = array_slice($data, -(max($ema_periods) * 2)); // We only need to work with a small portion of the dataset here

        $most_recent_data = end($data);

        if (!isset($most_recent_data->{'ema_' . $ema_periods[0]})) {
            $close_prices = [];
            foreach ($data as $row) {
                $close_prices[] = $row->close;
            }

            foreach ($ema_periods as $ema_period) {
                if ($ema_data = trader_ema($close_prices, $ema_period)) {
                    foreach ($ema_data as $key => $ema) {
                        $data[$key]->{'ema_' . $ema_period} = $ema;
                    }
                }
            }

            $this->setData($data);
        }

        return $data;
    }

    /**
     * @return float
     */
    protected function getChoppinessIndex(): float {
        $data = $this->getData();

        $choppiness_index = new choppiness_index();
        $index = $choppiness_index->get($data);

        return round($index);
    }

    /**
     * @return string
     */
    protected function getAtrDirection(): string {
        $data = $this->getData();

        $highs = [];
        $lows = [];
        $closes = [];

        foreach ($data as $row) {
            $highs[] = $row->high;
            $lows[] = $row->low;
            $closes[] = $row->close;
        }

        if ($atr_data = trader_atr($highs, $lows, $closes, 3)) {
            foreach ($atr_data as $key => $atr) {
                $data[$key]->atr = $atr;
            }
        }

        $last_two_data_points = array_slice($data, -2);

        $atr_1 = round($last_two_data_points[0]->atr, 4);
        $atr_2 = round($last_two_data_points[1]->atr, 4);

        if ($atr_1 === $atr_2) {
            return 'sideways';
        } else if ($atr_1 > $atr_2) {
            return 'down';
        } else {
            return 'up';
        }
    }

    /**
     * @param array $data
     */
    public function setData(array $data = []) {
        if ($data === []) {
            $this->data = $this->currency_pair->getData($this->data_fetch_size);
        } else {
            // Used by test cases
            $this->data = $data;
        }
    }

    /**
     *
     */
    protected function getData() {
        if ($this->data === []) {
            $this->setData();
        }
        return $this->data;
    }

    /**
     * @param \_pair $currency_pair
     */
    public function setPair(_pair $currency_pair) {
        $this->currency_pair = $currency_pair;
    }

    /**
     * @param bool $test
     */
    public function setTest(bool $test = true) {
        $this->testing = $test;
    }

    /**
     * @return bool
     */
    protected function isTest(): bool {
        return $this->testing;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }
}