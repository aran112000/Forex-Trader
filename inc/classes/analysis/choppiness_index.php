<?php

/**
 * The Choppiness Index is designed to measure the market's trendiness
 * (values below 20.00) versus the market's choppiness (values above
 * 60.00). When the indicator is reading values near 100, the market is
 * considered to be in choppy consolidation. The lower the value of the
 * Choppiness Index, the more the market is trending. The period supplied
 * by the user dictates how many bars are used to compute the index.
 *
 *   Power Trends requires the following settings:
 *   - Period = 4
 *   - Trend level = 20
 *   - Choppy level = 60
 *
 *   Anything less than 60 is okay for trading on under the Power Trends strategy
 *
 * Class choppiness_index
 */
final class choppiness_index {

    private $period = null;
    private $data = null;
    private $data_with_extra_period = null;

    /**
     * @param array $data - Newest data should be at the end of the array
     * @param int   $period
     *
     * @return float
     */
    public function get(array $data, int $period = 4): float {
        $this->setPeriod($period);
        $this->setPeriodData($data);

        return 100 * log10($this->getAtrSum() / ($this->getMaxHigh() - $this->getMinLow())) / log10($this->period);
    }

    /**
     * @return float
     */
    private function getMaxHigh(): float {
        $high = null;
        foreach ($this->data as $row) {
            if ($high === null || $high < $row->high) {
                $high = $row->high;
            }
        }

        return $high;
    }

    /**
     * @return float
     */
    private function getMinLow(): float {
        $low = null;
        foreach ($this->data as $row) {
            if ($low === null || $low > $row->low) {
                $low = $row->low;
            }
        }

        return $low;
    }

    /**
     * @return float
     */
    private function getAtrSum(): float {
        $highs = [];
        $lows = [];
        $closes = [];

        foreach ($this->data_with_extra_period as $row) {
            $highs[] = $row->high;
            $lows[] = $row->low;
            $closes[] = $row->close;
        }

        $atrs = trader_atr($highs, $lows, $closes, 1); // ATR (1 period)

        $sum = 0;
        foreach ($atrs as $atr) {
            $sum += $atr;
        }

        return $sum;
    }

    /**
     * @param int $period
     */
    private function setPeriod(int $period) {
        if ($this->period === null) {
            $this->period = $period;
        }
    }

    /**
     * @param array $full_data
     */
    private function setPeriodData(array $full_data) {
        if ($this->data === null) {
            $this->data = array_slice($full_data, (-1 * abs($this->period))); // Get the last X elements only (end element is the most recent)
            $this->data_with_extra_period = array_slice($full_data, (-1 * abs($this->period+1))); // Get the last X elements only (end element is the most recent)
        }
    }
}