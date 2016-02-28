<?php

/**
 * Class ema_50
 *
 * Because humans like to trade around round numbers
 */
class whole_number extends _base_analysis {

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = 'minor';

    /**
     * @var int
     */
    protected $data_fetch_size = 2;

    /**
     * @return array
     */
    function doAnalyse(): array {
        $data = $this->getData();
        $score = 0;

        if (!empty($data)) {
            $latest_data = end($data);
            $decimal_parts = explode('.', $latest_data->close, 2);
            $decimal_digits = (string) str_pad((string) $decimal_parts[1], 5, '0'); // Ensure we're working with 5 decimal places

            $number_of_zeros = substr_count($decimal_digits, '0');

            if ($number_of_zeros == 5) {
                $score = 1;
            } else if ($number_of_zeros == 4) {
                $score = 1;
            } else if ($number_of_zeros == 3) {
                $score = .9;
            } else if ($number_of_zeros == 2) {
                $score = .75;
            }
        }

        return [
            // This doesn't give any indication of a direction
            'buy' => $score,
            'sell' => $score,
        ];
    }
}