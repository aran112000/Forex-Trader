<?php

/**
 * Class power_trends
 */
class power_trends extends _base_analysis {

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
        $data = $this->getEmas([3, 7, 50]);
        $latest_day = end($data);

        if ($latest_day->high === $latest_day->low) {
            return false;
        }

        if ($latest_day->ema_3 > $latest_day->ema_7) {
            if ($latest_day->ema_7 > $latest_day->ema_50) {
                if ($latest_day->getDirection() === 'down' || $latest_day->getDirection() === 'neutral') {

                    $choppiness = $this->getChoppinessIndex();

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
        $data = $this->getEmas([3, 7, 50]);
        $latest_day = end($data);

        if ($latest_day->high === $latest_day->low) {
            return false;
        }

        if ($latest_day->ema_3 < $latest_day->ema_7) {
            if ($latest_day->ema_7 < $latest_day->ema_50) {
                if ($latest_day->getDirection() === 'up' || $latest_day->getDirection() === 'neutral') {

                    $choppiness = $this->getChoppinessIndex();

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