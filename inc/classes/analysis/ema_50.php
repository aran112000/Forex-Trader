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
        if (!empty($data)) {
            $this->addEmaToData($data);

            $latest_data = end($data);
            if (isset($latest_data['ema_50'])) {
                $current_distance_from_ema = number_format(abs($latest_data['close'] - $latest_data['ema_50']), 5, '.', '');

                if ($current_distance_from_ema <= 0.00003) {
                    return ($current_distance_from_ema == .00003 ? .6 : ($current_distance_from_ema == .00002 ? .75 : 1));
                }
            }
        }

        return 0;
    }

    /**
     * @param array $data
     */
    protected function addEmaToData(array &$data) {
        $exit_prices = [];

        foreach ($data as $row) {
            $exit_prices[] = $row['close'];
        }

        if ($ema_data = trader_ema($exit_prices, 50)) {
            foreach ($ema_data as $key => $ema_50) {
                if (isset($data[$key])) {
                    $data[$key]['ema_50'] = $ema_50;
                }
            }
        }
    }
}