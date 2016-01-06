<?php

/**
 * Class doji
 */
class doji extends _base_analysis {

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
    protected $data_fetch_size = 3;

    /**
     * @return array
     */
    public function doAnalyse(): array {
        $data = $this->getData();
        $score = [
            'buy' => 0,
            'sell' => 0,
        ];

        if (!empty($data) && isset($data[2])) {
            $last_two_data_points = array_splice($data, -2);

            $prior = $last_two_data_points[0];
            $last = $last_two_data_points[1];

            if ($last->close === $last->low || $last->close === $last->high) {
                // We closed at the most extreme endpoint, lets now trade on this
                return $score;
            }

            $prior_price_difference = abs($prior->open - $prior->close);
            $last_price_difference = abs($last->open - $last->close);

            $price_difference = $prior_price_difference - $last_price_difference;

            if ($price_difference > 0.00002) {
                if ($last_price_difference < 0.00004) {
                    if ($price_difference <= 0.0001) {
                        $score = 1;
                    } else if ($price_difference <= 0.0002) {
                        $score = .9;
                    } else if ($price_difference <= 0.0003) {
                        $score = .8;
                    } else {
                        $score = .7;
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
}