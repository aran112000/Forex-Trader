<?php

/**
 * Class reversals
 */
class reversals extends _base_analysis {

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
        /*$data = $this->getData();
        $latest_day = end($data);*/

        if ($this->getChoppinessIndex() < 60) {
            if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isShortEntry() {
        /*$data = $this->getData();
        $latest_day = end($data);*/

        if ($this->getChoppinessIndex() < 60) {
            if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {
                return true;
            }
        }

        return false;
    }
}