<?php

/**
 * Class bounces
 */
class bounces extends _base_analysis {

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var int
     */
    protected $data_fetch_size = 250;

    /**
     * @param \_pair $currency_pair
     */
    public function setPair(_pair $currency_pair) {
        parent::setPair($currency_pair);

        $this->currency_pair->data_fetch_time = '1d';
    }

    /**
     * @return bool
     */
    protected function isLongEntry(): bool {
        $data = $this->getEmas([20, 50]);
        $latest_day = end($data);

        if ($latest_day->high === $latest_day->low) {
            return false;
        }

        $choppiness = $this->getChoppinessIndex();

        // Look for an 20 EMA bounce
        if ($latest_day->ema_20 > $latest_day->ema_50) {
            if ($latest_day->low < $latest_day->ema_20) {
                if ($latest_day->open > $latest_day->ema_20 && $latest_day->close > $latest_day->ema_20) {
                    if ($choppiness <= 60 && $choppiness >= 20) {
                        $atr_direction = $this->getAtrDirection();
                        if ($atr_direction === 'down' || $atr_direction === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        // Look for an 50 EMA bounce
        if ($latest_day->ema_20 > $latest_day->ema_50) {
            if ($latest_day->low < $latest_day->ema_50) {
                if ($latest_day->open > $latest_day->ema_50 && $latest_day->close > $latest_day->ema_50) {
                    if ($choppiness <= 60 && $choppiness >= 20) {
                        $atr_direction = $this->getAtrDirection();
                        if ($atr_direction === 'down' || $atr_direction === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isShortEntry() {
        $data = $this->getEmas([20, 50]);
        $latest_day = end($data);

        if ($latest_day->high === $latest_day->low) {
            return false;
        }

        $choppiness = $this->getChoppinessIndex();

        // Look for an 20 EMA bounce
        if ($latest_day->ema_20 < $latest_day->ema_50) {
            if ($latest_day->high > $latest_day->ema_20) {
                if ($latest_day->open < $latest_day->ema_20 && $latest_day->close < $latest_day->ema_20) {
                    if ($choppiness <= 60 && $choppiness >= 20) {
                        $atr_direction = $this->getAtrDirection();
                        if ($atr_direction === 'down' || $atr_direction === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        // Look for an 50 EMA bounce
        if ($latest_day->ema_20 < $latest_day->ema_50) {
            if ($latest_day->high > $latest_day->ema_50) {
                if ($latest_day->open < $latest_day->ema_50 && $latest_day->close < $latest_day->ema_50) {
                    if ($choppiness <= 60 && $choppiness >= 20) {
                        $atr_direction = $this->getAtrDirection();
                        if ($atr_direction === 'down' || $atr_direction === 'sideways') {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}