<?php

/**
 * Class ema_50
 */
class ema_50 extends _base_analysis {

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = 'major';

    /**
     * @var int
     */
    protected $data_fetch_size = 10080; // 7 days

    /**
     * @return float
     */
    function doAnalyse(): float {
        $data = $this->getData();
        $this->addEmaToData($data);

        $latest_data = end($data);

        $current_distance_from_ema = number_format(abs($latest_data['exit_price'] - $latest_data['ema_50']), 5, '.', '');

        if ($current_distance_from_ema <= 0.00003) {
            return ($current_distance_from_ema == .00003 ? .6 : ($current_distance_from_ema == .00002 ? .75 : 1));
        }

        return 0;
    }

    /**
     * @param array $data
     */
    protected function addEmaToData(array &$data) {
        $exit_prices = [];

        foreach ($data as $row) {
            $exit_prices[] = $row['exit_price'];
        }

        foreach (trader_ema($exit_prices, 50) as $key => $ema_50) {
            if (isset($data[$key])) {
                $data[$key]['ema_50'] = $ema_50;
            }
        }
    }
}