<?php

/**
 * Class doji
 */
class doji extends _base_analysis {

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = 'major';

    /**
     * @var int
     */
    protected $data_fetch_size = 3;

    /**
     * @return float
     */
    public function doAnalyse(): float {
        $data = $this->getData();

        if (isset($data[2])) {
            $last_price_difference = abs($data[1]['entry_price'] - $data[1]['exit_price']);
            $prior_price_difference = abs($data[0]['entry_price'] - $data[0]['exit_price']);

            $price_difference = $prior_price_difference - $last_price_difference;
            if ($price_difference > 0.00002) {
                if ($last_price_difference < 0.00004) {
                    if ($price_difference <= 0.0001) {
                        return 1;
                    } else if ($price_difference <= 0.0002) {
                        return .9;
                    } else if ($price_difference <= 0.0003) {
                        return .8;
                    }

                    return .7;
                }
            }

        }

        return 0;
    }
}