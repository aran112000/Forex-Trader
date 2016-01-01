<?php

/**
 * Class high_low_test
 */
class high_low_test extends _base_analysis {

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
        return $this->doHighTest() + $this->doLowTest(); // Combined score
    }

    /**
     * @return float
     */
    private function doHighTest(): float {
        $i = 0;
        $initial_candle_body_size = $percentage = 0;
        $data = $this->getData();

        foreach ($data as $row) {
            $i++;
            if ($i == 1) {
                // Skip the first row as this is most likely the current time period and still changing
                continue;
            } else if ($i == 2) {
                $top_candle_price = ($row['entry_price'] > $row['exit_price'] ? $row['entry_price'] : $row['exit_price']);

                $wick_size = $row['max_price'] - $top_candle_price;
                $total_size = $row['max_price'] - $row['min_price'];

                $initial_candle_body_size = round(abs($row['entry_price'] - $row['exit_price']), 5);

                if ($total_size == 0) {
                    return 0;
                }
                $percentage = ($wick_size / $total_size);
                if ($percentage <= .666) {
                    return 0;
                }
            } else {
                $candle_body_size = round(abs($row['entry_price'] - $row['exit_price']), 5);

                if ($initial_candle_body_size == 0) {
                    return 0;
                }
                $percentage_candle_body_size_increase = (($candle_body_size / $initial_candle_body_size) * 100);

                if ($percentage_candle_body_size_increase >= 33.3) {
                    // The last candle was at least 1/3 smaller than the one that came before it - This looks like a reversal
                    return $percentage;
                }
            }
        }

        return 0;
    }

    /**
     * @return float
     */
    private function doLowTest(): float {
        $i = 0;
        $initial_candle_body_size = $percentage = 0;
        $data = $this->getData();

        foreach ($data as $row) {
            $i++;
            if ($i == 1) {
                // Skip the first row as this is most likely the current time period and still changing
                continue;
            } else if ($i == 2) {
                $bottom_candle_price = ($row['exit_price'] < $row['entry_price'] ? $row['exit_price'] : $row['entry_price']);

                $wick_size = $bottom_candle_price - $row['min_price'];
                $total_size = $row['max_price'] - $row['min_price'];

                $initial_candle_body_size = round(abs($row['entry_price'] - $row['exit_price']), 5);

                if ($total_size == 0) {
                    return 0;
                }
                $percentage = ($wick_size / $total_size);
                if ($percentage <= .666) {
                    return 0;
                }
            } else if ($i == 3) {
                $candle_body_size = round(abs($row['entry_price'] - $row['exit_price']), 5);

                if ($initial_candle_body_size == 0) {
                    return 0;
                }
                $percentage_candle_body_size_increase = (($candle_body_size / $initial_candle_body_size) * 100);

                if ($percentage_candle_body_size_increase >= 33.3) {
                    // The last candle was at least 1/3 smaller than the one that came before it - This looks like a reversal
                    return $percentage;
                }
            }
        }

        return 0;
    }
}