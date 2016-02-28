<?php

/**
 * Class ema_50
 */
class ema_50 extends _base_analysis {

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = 'major';

    /**
     * @var int
     */
    protected $data_fetch_size = 10080; // 7 days

    const MINIMUM_DISTANCE_FROM_EMA = .00005;

    /**
     * @return array
     */
    function doAnalyse(): array {
        $data = $this->getData();
        $score = [
            'buy' => 0,
            'sell' => 0,
        ];

        if (!empty($data)) {
            $this->addEmaToData($data);
            $latest_data = end($data);

            if (isset($latest_data->ema_50)) {
                $current_distance_from_ema = abs($latest_data->close - $latest_data->ema_50);

                if ($current_distance_from_ema <= self::MINIMUM_DISTANCE_FROM_EMA) {
                    // TODO; Update to work with buy/sell scores
                    $score = ((self::MINIMUM_DISTANCE_FROM_EMA / ($current_distance_from_ema + 1)) / 5);

                    return [
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
    protected function addEmaToData(array &$data) {
        $exit_prices = [];

        foreach ($data as $row) {
            $exit_prices[] = $row->close;
        }

        if ($ema_data = trader_ema($exit_prices, 50)) {
            foreach ($ema_data as $key => $ema_50) {
                if (isset($data[$key])) {
                    $data[$key]->ema_50 = $ema_50;
                }
            }
        }
    }
}