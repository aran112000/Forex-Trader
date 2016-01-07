<?php

/**
 * Class _pair
 */
abstract class _pair {

    protected $enabled = true;

    public $base_currency  = null;
    public $quote_currency = null;

    public $data_fetch_time = '1m';

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
    public function getData(int $limit = 20, $order = 'DESC'): array {
        $return = [];

        $pair = $this->base_currency . '_' . $this->quote_currency;
        if ($res = db::query('SELECT * FROM pricing_' . $this->data_fetch_time . ' WHERE pair=\'' . db::esc($pair) . '\' ORDER BY timekey ' . strtoupper($order) . ' LIMIT ' . $limit)) {
            while ($row = db::fetch($res)) {
                $class = new avg_price_data();
                $class->pair = $pair;
                $class->timekey = $row['timekey'];
                $class->entry_time = $row['entry_time'];
                $class->exit_time = $row['exit_time'];
                $class->open = $row['open'];
                $class->close = $row['close'];
                $class->high = $row['high'];
                $class->low = $row['low'];
                $class->volume = $row['volume'];

                $return[] = $class;
            }

            // Due to the limit we need to now reverse the order of our data so the newest is at the end
            $return = array_reverse($return);
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