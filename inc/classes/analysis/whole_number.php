<?php

/**
 * Class ema_50
 *
 * Because humans like to trade around round numbers
 */
class whole_number extends _base_analysis {

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = 'minor';

    /**
     * @var int
     */
    protected $data_fetch_size = 2;

    /**
     * @return float
     */
    function doAnalyse(): float {
        $data = $this->getData();

        $latest_data = end($data);

        $decimal_parts = explode('.', $latest_data['exit_price'], 2);
        $decimal_digits = $decimal_parts[1];

        if (strlen($decimal_digits) === 4) {
            if (substr($decimal_digits, -1) === 0) {
                // This is a large round number
                return 1;
            }

            // This is a round number
            return .75;
        }

        return 0;
    }
}