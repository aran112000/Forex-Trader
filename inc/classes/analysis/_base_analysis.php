<?php

/**
 * Class _base_analysis
 */
abstract class _base_analysis {

    /**
     * @var string|null - 'major' OR 'minor'
     */
    public $signal_strength = null;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var bool
     */
    private $testing = false;

    /**
     * @var _pair|null
     */
    protected $currency_pair = null;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int
     */
    protected $data_fetch_size = 50;

    /**
     * @param array $data
     */
    public function setData(array $data = []) {
        if ($data === []) {
            $this->data = $this->currency_pair->getOneMinuteData($this->data_fetch_size);
        } else {
            // Used by test cases
            $this->data = $data;
        }
    }

    /**
     *
     */
    protected function getData() {
        if ($this->data === []) {
            $this->setData();
        }
        return $this->data;
    }

    /**
     * @param \_pair $currency_pair
     */
    public function setPair(_pair $currency_pair) {
        $this->currency_pair = $currency_pair;
    }

    /**
     * @param bool $test
     */
    public function setTest(bool $test = true) {
        $this->testing = $test;
    }

    /**
     * @return bool
     */
    protected function isTest(): bool {
        return $this->testing;
    }

    /**
     * @return float
     */
    abstract function doAnalyse(): float;

    /**
     * @return bool
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }
}