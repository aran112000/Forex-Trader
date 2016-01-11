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
        $data = array_slice($data, -2);

        if ($direction === 'long') {
            $type = 'Buy';
            $entry = $data[0]->high + 0.0002;
            $exit = $data[0]->low - 0.0002;
        } else {
            $type = 'Sell';
            $entry = $data[0]->low - 0.0002;
            $exit = $data[0]->high + 0.0002;
        }

        $pip_difference = (($entry - $exit) * 10000);

        $account = new account();
        $balance = $account->getBalance();

        $amount = $balance / $pip_difference;

        return [
            'type' => $type,
            'entry' => $entry,
            'exit' => $exit,
            'current_balance' => $balance,
            'pip_difference' => $pip_difference,
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

        if (!isset($data[0]->{'ema_' . $ema_periods[0]})) {
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
        /**@var avg_price_data $latest_day*/
        $latest_day = end($data);

        $valid_signal = null;

        // Look for a 20 EMA bounce
        if ($latest_day->ema_20 <= $latest_day->ema_50) {
            // 20 EMA is above 50 EMA
            $valid_signal = false;
        } else if ($latest_day->low >= $latest_day->ema_20) {
            // Daily low is lower than the 20 EMA
            $valid_signal = false;
        } else if ($latest_day->open <= $latest_day->ema_20 && $latest_day->close <= $latest_day->ema_20) {
            // Daily open and close price is higher than the 20 EMA
            $valid_signal = false;
        } else if ($this->getChoppinessIndex() >= 60) {
            // The market is not choppy (Choppiness index is under 60)
            $valid_signal = false;
        } else if ($this->getAtrDirection() === 'up') {
            // The market is not accelerating (ATR is pointing sideways or down)
            $valid_signal = false;
        } else {
            $valid_signal = true;
        }

        if ($valid_signal === false) {
            // Look for a 50 EMA bounce
            if ($latest_day->ema_20 <= $latest_day->ema_50) {
                // 20 EMA is above 50 EMA
                $valid_signal = false;
            } else if ($latest_day->low >= $latest_day->ema_50) {
                // Daily low is lower than the 50 EMA
                $valid_signal = false;
            } else if ($latest_day->open <= $latest_day->ema_50 && $latest_day->close <= $latest_day->ema_50) {
                // Daily open and close price is higher than the 20 EMA
                $valid_signal = false;
            } else if ($this->getChoppinessIndex() >= 60) {
                // The market is not choppy (Choppiness index is under 60)
                $valid_signal = false;
            } else if ($this->getAtrDirection() === 'up') {
                // The market is not accelerating (ATR is pointing sideways or down)
                $valid_signal = false;
            } else {
                $valid_signal = true;
            }
        }

        return $valid_signal;
    }

    /**
     * @return bool
     */
    protected function isShortEntry() {
        $data = $this->getEmas();
        /**@var avg_price_data $latest_day */
        $latest_day = end($data);

        $valid_signal = null;

        // Look for a 20 EMA bounce
        if ($latest_day->ema_20 >= $latest_day->ema_50) {
            // 20 EMA is below 50 EMA
            $valid_signal = false;
        } else if ($latest_day->high <= $latest_day->ema_20) {
            // Daily high is higher than the 20 EMA
            $valid_signal = false;
        } else if ($latest_day->open >= $latest_day->ema_20 && $latest_day->close >= $latest_day->ema_20) {
            // Daily open and close price is lower than the 20 EMA
            $valid_signal = false;
        } else if ($this->getChoppinessIndex() >= 60) {
            // The market is not choppy (Choppiness index is under 60)
            $valid_signal = false;
        } else if ($this->getAtrDirection() === 'up') {
            // The market is not accelerating (ATR is pointing sideways or down)
            $valid_signal = false;
        } else {
            $valid_signal = true;
        }

        if ($valid_signal === false) {
            // Look for a 50 EMA bounce
            if ($latest_day->ema_20 >= $latest_day->ema_50) {
                // 20 EMA is below 50 EMA
                $valid_signal = false;
            } else if ($latest_day->high <= $latest_day->ema_50) {
                // Daily high is higher than the 50 EMA
                $valid_signal = false;
            } else if ($latest_day->open >= $latest_day->ema_50 && $latest_day->close >= $latest_day->ema_50) {
                // Daily open and close price is lower than the 50 EMA
                $valid_signal = false;
            } else if ($this->getChoppinessIndex() >= 60) {
                // The market is not choppy (Choppiness index is under 60)
                $valid_signal = false;
            } else if ($this->getAtrDirection() === 'up') {
                // The market is not accelerating (ATR is pointing sideways or down)
                $valid_signal = false;
            } else {
                $valid_signal = true;
            }
        }

        return $valid_signal;
    }
}