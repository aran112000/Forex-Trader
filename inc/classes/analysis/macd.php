<?php

/**
 * Class macd
 */
class macd extends _base_analysis {

    const FAST_PERIOD   = 12; // Taken from FXCM trading station
    const SLOW_PERIOD   = 26; // Taken from FXCM trading station
    const SIGNAL_PERIOD = 9;  // Taken from FXCM trading station

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
    public function doAnalyse(): float {
        $data = $this->getData();
        if (!empty($data)) {
            $this->addMacdToData($data);

            $last_data_point = end($data);
            if (isset($last_data_point['macd'])) {
                $last_macd = abs($last_data_point['macd']);

                if ($last_macd >= 0.00012) {
                    return 1;
                } else if ($last_macd >= 0.00011) {
                    return .9;
                } else if ($last_macd >= 0.00010) {
                    return .8;
                } else if ($last_macd >= 0.00009) {
                    return .66;
                } else if ($last_macd >= 0.00008) {
                    return .5;
                } else if ($last_macd >= 0.00007) {
                    return .43;
                } else if ($last_macd >= 0.00006) {
                    return .35;
                }
            }
        }

        return 0;
    }

    /**
     * @param array $data
     */
    private function addMacdToData(array &$data) {
        $exit_prices = [];
        foreach ($data as $row) {
            $exit_prices[] = $row['close'];
        }

        if ($macd_data = trader_macd($exit_prices, self::FAST_PERIOD, self::SLOW_PERIOD, self::SIGNAL_PERIOD)) {
            $divergence_data = $macd_data[2];
            foreach ($divergence_data as $key => $macd_divergence) {
                if (isset($data[$key])) {
                    $macd_divergence = number_format($macd_divergence, 5, '.', '');
                    $data[$key]['macd'] = $macd_divergence;
                }
            }
        }
    }
}