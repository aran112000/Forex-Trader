<?php

/**
 * Class power_trends
 */
class power_trends extends _base_analysis {

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
        if (gmdate('H') >= 22) {
            $data = $this->getData();
            array_pop($data);

            $this->setData($data);
        }

        if ($this->isShortEntry()) {
            email::send('Power Trends - Short entry found', 'A short entry opportunity for ' . $this->currency_pair->getPairName('/'));
        } else if ($this->isLongEntry()) {
            email::send('Power Trends - Long entry found', 'A long entry opportunity for ' . $this->currency_pair->getPairName('/'));
        }

        return [
            'sell' => 0,
            'buy' => 0,
        ];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool {
        $response = parent::isEnabled();
        if (!$response) {
            return $response;
        }

        // TODO Remove - This is only a test
        if (!cli) {
            return true;
        }

        // This is ONLY enabled once each day just before 22:00 (a couple of minutes allows for any time delays)
        if (gmdate('H:i') === '21:58' && $this->last_run_date !== gmdate('d/m/Y')) {
            $this->last_run_date = gmdate('d/m/Y');

            return true;
        }

        return false;
    }

    /**
     * @param array $ema_periods
     *
     * @return mixed
     */
    private function getEmas(array $ema_periods = [3, 7, 50]): array {
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

        $last_two_data_points = array_splice($data, -2);

        if ($last_two_data_points[0]->atr >= $last_two_data_points[1]->atr) {
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

        if ($latest_day->ema_3 <= $latest_day->ema_7) {
            return false;
        }
        if ($latest_day->ema_7 <= $latest_day->ema_50) {
            return false;
        }
        if ($latest_day->getDirection() !== 'down') {
            return false;
        }
        if ($this->getChoppinessIndex() >= 60) {
            return false;
        }
        if ($this->getAtrDirection() !== 'down') {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isShortEntry() {
        $data = $this->getEmas();
        /**@var avg_price_data $latest_day */
        $latest_day = end($data);

        if ($latest_day->ema_3 >= $latest_day->ema_7) {
            return false;
        }
        if ($latest_day->ema_7 >= $latest_day->ema_50) {
            return false;
        }
        if ($latest_day->getDirection() !== 'up') {
            return false;
        }
        if ($this->getChoppinessIndex() >= 60) {
            return false;
        }
        if ($this->getAtrDirection() !== 'up') {
            return false;
        }

        return true;
    }
}