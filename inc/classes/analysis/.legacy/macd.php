<?php

/**
 * Class macd
 */
class macd extends _base_analysis {

    /**
     * @var bool
     */
    protected $enabled = false;

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
     * @return array
     */
    public function doAnalyse(): array {
        $data = $this->getData();
        $score = [
            'buy' => 0,
            'sell' => 0,
        ];

        if (!empty($data)) {
            $this->addMacdToData($data);

            $last_data_point = end($data);
            if (isset($last_data_point->macd)) {
                $last_macd = abs($last_data_point->macd);

                if ($last_macd >= 0.00009) {
                    if ($last_macd >= 0.00012) {
                        $score = 1;
                    } else if ($last_macd >= 0.00011) {
                        $score = .85;
                    } else if ($last_macd >= 0.00010) {
                        $score = .7;
                    } else {
                        $score = .5;
                    }

                    return [
                        // TODO; Update to work with buy/sell scores
                        'buy' => $score,
                        'sell' => $score,
                    ];
                }
            }
        }

        return $score;
    }

    /**
     * @param array $data
     */
    private function addMacdToData(array &$data) {
        $exit_prices = [];
        foreach ($data as $row) {
            $exit_prices[] = $row->close;
        }

        if ($macd_data = trader_macd($exit_prices, self::FAST_PERIOD, self::SLOW_PERIOD, self::SIGNAL_PERIOD)) {
            $divergence_data = $macd_data[2];
            foreach ($divergence_data as $key => $macd_divergence) {
                if (isset($data[$key])) {
                    $macd_divergence = number_format($macd_divergence, 5, '.', '');
                    $data[$key]->macd = $macd_divergence;
                }
            }
        }
    }
}