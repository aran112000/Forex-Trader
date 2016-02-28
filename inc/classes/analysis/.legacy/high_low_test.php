<?php

/**
 * Class high_low_test
 */
class high_low_test extends _base_analysis {

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
        $high = $this->doHighTest();
        $low = $this->doLowTest();

        return [
            'buy' => $high['buy'] + $low['buy'],
            'sell' => $high['sell'] + $low['sell'],
        ];
    }

    /**
     * @return array
     */
    private function doHighTest(): array {
        $data = $this->getData();
        $score = [
            'buy' => 0,
            'sell' => 0,
        ];

        if (!empty($data)) {
            $i = 0;
            $initial_candle_body_size = $percentage = 0;

            $data = array_reverse($data);

            foreach ($data as $row) {
                $i++;
                if ($i == 1) {
                    $top_candle_price = ($row->open > $row->close ? $row->open : $row->close);

                    $wick_size = $row->high - $top_candle_price;
                    $total_size = $row->high - $row->low;

                    $initial_candle_body_size = round(abs($row->open - $row->close), 5);

                    if ($total_size == 0) {
                        return $score;
                    }
                    $percentage = ($wick_size / $total_size);
                    if ($percentage <= .666) {
                        return $score;
                    }
                } else if ($i == 2) {
                    $candle_body_size = round(abs($row->open - $row->close), 5);

                    if ($initial_candle_body_size == 0) {
                        return $score;
                    }
                    $percentage_candle_body_size_increase = (($candle_body_size / $initial_candle_body_size) * 100);

                    if ($percentage_candle_body_size_increase >= 33.3) {
                        // The last candle was at least 1/3 smaller than the one that came before it - This looks like a reversal
                        return [
                            // TODO; Update to work with buy/sell scores
                            'buy' => $percentage,
                            'sell' => $percentage,
                        ];
                    }
                } else {
                    break;
                }
            }
        }

        return $score;
    }

    /**
     * @return array
     */
    private function doLowTest(): array {
        $data = $this->getData();
        $score = [
            'buy' => 0,
            'sell' => 0,
        ];

        if (!empty($data)) {
            $i = 0;
            $initial_candle_body_size = $percentage = 0;

            $data = array_reverse($data);

            foreach ($data as $row) {
                $i++;
                if ($i == 1) {
                    $bottom_candle_price = ($row->close < $row->open ? $row->close : $row->open);

                    $wick_size = $bottom_candle_price - $row->low;
                    $total_size = $row->high - $row->low;

                    $initial_candle_body_size = round(abs($row->open - $row->close), 5);

                    if ($total_size == 0) {
                        return $score;
                    }
                    $percentage = ($wick_size / $total_size);
                    if ($percentage <= .666) {
                        return $score;
                    }
                } else if ($i == 2) {
                    $candle_body_size = round(abs($row->open - $row->close), 5);

                    if ($initial_candle_body_size == 0) {
                        return $score;
                    }
                    $percentage_candle_body_size_increase = (($candle_body_size / $initial_candle_body_size) * 100);

                    if ($percentage_candle_body_size_increase >= 33.3) {
                        // The last candle was at least 1/3 smaller than the one that came before it - This looks like a reversal
                        return [
                            // TODO; Update to work with buy/sell scores
                            'buy' => $percentage,
                            'sell' => $percentage,
                        ];
                    }
                } else {
                    break;
                }
            }
        }

        return $score;
    }
}