<?php

/**
 * Class bounces
 */
class bounces extends _base_analysis {

    private $last_run_date = null;

    /**
     * @var int
     */
    protected $data_fetch_size = 250;

    /**
     * @param \_pair $currency_pair
     */
    public function setPair(_pair $currency_pair) {
        parent::setPair($currency_pair);

        $this->currency_pair->data_fetch_time = '1d';
    }

    /**
     * @return array
     */
    public function doAnalyse(): array {
        log::write('Bounces called', log::INFO);
        $direction = false;
        $trade_details = [];

        // This is ONLY enabled once each day just before 22:00 (a couple of minutes allows for any time delays)
        if ((defined('testing') && testing) || (gmdate('H:i') === '21:59' && $this->last_run_date !== gmdate('d/m/Y'))) {
            log::write('Checking for Bounces as time = 21:59', log::DEBUG);

            $this->last_run_date = gmdate('d/m/Y');

            if ($this->isShortEntry()) {
                $direction = 'short';
                $trade_details = $this->getTradeDetails($direction);
            } else if ($this->isLongEntry()) {
                $direction = 'long';
                $trade_details = $this->getTradeDetails($direction);
            }

            if (!defined('testing') || !testing) {
                if ($direction !== false) {
                    log::write($direction . ' trade found', log::DEBUG);
                    email::send('Bounces - Entry signal found', '<p>A ' . $direction . ' entry opportunity for ' . $this->currency_pair->getPairName('/') . '</p><p><pre>' . print_r($trade_details, true) . '</pre></p>', 'cdtreeks@gmail.com,jainikadrenkhan@gmail.com');

                    return $trade_details;
                } else {
                    log::write('Bounces - No trade found', log::DEBUG);
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
    private function getTradeDetails(string $direction): array {
        $data = $this->getData();

        /**@var avg_price_data $latest_day*/
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

        $pip_difference = get::pip_difference($entry, $stop);
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
    private function getEmas(array $ema_periods = [20, 50]): array {
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
    private function getChoppinessIndex(): float {
        $data = $this->getData();

        $choppiness_index = new choppiness_index();
        $index = $choppiness_index->get($data);

        return $index;
    }

    /**
     * @return string
     */
    private function getAtrDirection(): string {
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

        if ($last_two_data_points[0]->atr == $last_two_data_points[1]->atr) {
            return 'sideways';
        } else if ($last_two_data_points[0]->atr >= $last_two_data_points[1]->atr) {
            return 'down';
        } else {
            return 'up';
        }
    }

    /**
     * @return bool
     */
    protected function isLongEntry(): bool {
        $data = $this->getEmas();
        $latest_day = end($data);

        if ($latest_day->high === $latest_day->low) {
            return false;
        }

        // Look for an 20 EMA bounce
        if ($latest_day->ema_20 > $latest_day->ema_50) {
            if ($latest_day->low < $latest_day->ema_20) {
                if ($latest_day->open > $latest_day->ema_20 && $latest_day->close > $latest_day->ema_20) {
                    if ($this->getChoppinessIndex() < 60) {
                        if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        // Look for an 50 EMA bounce
        if ($latest_day->ema_20 > $latest_day->ema_50) {
            if ($latest_day->low < $latest_day->ema_50) {
                if ($latest_day->open > $latest_day->ema_50 && $latest_day->close > $latest_day->ema_50) {
                    if ($this->getChoppinessIndex() < 60) {
                        if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isShortEntry() {
        $data = $this->getEmas();
        $latest_day = end($data);

        if ($latest_day->high === $latest_day->low) {
            return false;
        }

        // Look for an 20 EMA bounce
        if ($latest_day->ema_20 < $latest_day->ema_50) {
            if ($latest_day->high > $latest_day->ema_20) {
                if ($latest_day->open < $latest_day->ema_20 && $latest_day->close < $latest_day->ema_20) {
                    if ($this->getChoppinessIndex() < 60) {
                        if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        // Look for an 50 EMA bounce
        if ($latest_day->ema_20 < $latest_day->ema_50) {
            if ($latest_day->high > $latest_day->ema_50) {
                if ($latest_day->open < $latest_day->ema_50 && $latest_day->close < $latest_day->ema_50) {
                    if ($this->getChoppinessIndex() < 60) {
                        if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}