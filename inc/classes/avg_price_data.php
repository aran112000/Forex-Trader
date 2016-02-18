<?php

/**
 * Class avg_price_data
 */
class avg_price_data {

    /**
     * @var string
     */
    public $pair = null;
    /**
     * @var float
     */
    public $high = null;
    /**
     * @var float
     */
    public $low = null;
    /**
     * @var float
     */
    public $open = null;
    /**
     * @var float
     */
    public $close = null;
    /**
     * @var float
     */
    public $spread = null;
    /**
     * @var int
     */
    public $timekey = null;
    /**
     * @var int
     */
    public $volume = null;
    /**
     * @var string
     */
    public $entry_time = null;
    /**
     * @var string
     */
    public $exit_time = null;
    /**
     * @var string
     */
    public $start_date_time = null;

    /**
     * @return string
     */
    public function getDirection(): string {
        return ($this->open > $this->close ? 'down' : ($this->open < $this->close ? 'up' : 'neutral'));
    }
}