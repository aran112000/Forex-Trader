<?php

/**
 * Class _pair
 */
abstract class _pair {

    protected $enabled = true;

    public $base_currency  = null;
    public $quote_currency = null;

    /**
     * _pair constructor.
     */
    public function __construct() {
        $this->setCurrencyPairs();
    }

    /**
     * @param string $delimiter
     *
     * @return string
     */
    public function getPairName($delimiter = '_'): string {
        return strtoupper($this->base_currency . $delimiter . $this->quote_currency);
    }

    /**
     * @param int    $limit
     * @param string $order
     *
     * @return mixed
     */
    public function getOneMinuteData(int $limit = 20, $order = 'DESC'): array {
        $return = [];

        $pair = $this->base_currency . '_' . $this->quote_currency;
        if ($res = db::query('SELECT * FROM pricing_1_minute WHERE pair=\'' . db::esc($pair) . '\' ORDER BY timekey ' . strtoupper($order) . ' LIMIT ' . $limit)) {
            while ($row = db::fetch($res)) {
                $return[] = $row;
            }
        }

        return $return;
    }

    /**
     * @param int $limit
     *
     * @return mixed
     */
    public function getFiveMinuteData(int $limit = 2): array {
        $return = [];

        $pair = $this->base_currency . '_' . $this->quote_currency;
        if ($res = db::query('SELECT * FROM 5_minute_view WHERE pair=\'' . db::esc($pair) . '\' ORDER BY timekey DESC LIMIT ' . $limit)) {
            while ($row = db::fetch($res)) {
                $return[] = $row;
            }
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }

    /**
     *
     */
    private function setCurrencyPairs() {
        $class = get_called_class();
        $class_parts = explode('_', $class, 2);

        $this->base_currency = strtoupper($class_parts[0]);
        $this->quote_currency = strtoupper($class_parts[1]);
    }
}