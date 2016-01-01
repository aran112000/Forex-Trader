<?php

/**
 * Class rsi
 */
class rsi extends _base_analysis {

    const NUMBER_OF_PERIODS = 14; // Taken from FXCM trading station

    CONST OVERBOUGHT = 70;
    CONST OVERSOLD = 30;
    CONST ALLOW_WITHIN_TOLERANCE = 5;

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
        $this->addRsiToData($data);

        $last_data_point = end($data);
        $last_rsi = $last_data_point['rsi'];

        if ($last_rsi >= (self::OVERBOUGHT - self::ALLOW_WITHIN_TOLERANCE)) {
            // Overbought - Signals a likely reversal soon
            if ($last_rsi >= self::OVERBOUGHT) {
                return 1;
            }

            return .7;
        } else if ($last_rsi <= (self::OVERSOLD + self::ALLOW_WITHIN_TOLERANCE)) {
            // Oversold - Signals a likely reversal soon
            if ($last_rsi <= self::OVERSOLD) {
                return 1;
            }

            return .7;
        }

        // Normal healthy market volume
        return 0;
    }

    /**
     * @param array $data
     */
    private function addRsiToData(array &$data) {
        $exit_prices = [];
        foreach ($data as $row) {
            $exit_prices[] = $row['exit_price'];
        }

        $rsi_data = trader_rsi($exit_prices, self::NUMBER_OF_PERIODS);
        foreach ($rsi_data as $key => $rsi) {
            if (isset($data[$key])) {
                $data[$key]['rsi'] = $rsi;
            }
        }
    }
}