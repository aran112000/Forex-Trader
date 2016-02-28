<?php

/**
 * Class rsi
 */
class rsi extends _base_analysis {

    /**
     * @var bool
     */
    protected $enabled = false;

    const NUMBER_OF_PERIODS = 14; // Taken from FXCM trading station

    CONST OVERBOUGHT = 70;
    CONST OVERSOLD = 30;
    CONST ALLOW_WITHIN_TOLERANCE = 3;

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
            $this->addRsiToData($data);

            $last_data_point = end($data);
            if (isset($last_data_point->rsi)) {
                $last_rsi = $last_data_point->rsi;

                if ($last_rsi >= (self::OVERBOUGHT - self::ALLOW_WITHIN_TOLERANCE)) {
                    // Overbought - Signals a likely reversal soon
                    if ($last_rsi >= self::OVERBOUGHT) {
                        $score['sell'] = 1;

                        return $score;
                    }

                    $score['sell'] = .6;

                    return $score;
                } else if ($last_rsi <= (self::OVERSOLD + self::ALLOW_WITHIN_TOLERANCE)) {
                    // Oversold - Signals a likely reversal soon
                    if ($last_rsi <= self::OVERSOLD) {
                        $score['buy'] = 1;

                        return $score;
                    }

                    $score['buy'] = .6;

                    return $score;
                }
            }
        }

        return $score;
    }

    /**
     * @param array $data
     */
    private function addRsiToData(array &$data) {
        $exit_prices = [];
        foreach ($data as $row) {
            $exit_prices[] = $row->close;
        }

        if ($rsi_data = trader_rsi($exit_prices, self::NUMBER_OF_PERIODS)) {
            foreach ($rsi_data as $key => $rsi) {
                if (isset($data[$key])) {
                    $data[$key]->rsi = $rsi;
                }
            }
        }
    }
}