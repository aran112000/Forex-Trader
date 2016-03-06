<?php

/**
 * Class _pair
 */
abstract class _pair {

    protected $enabled = true;

    public $base_currency = null;
    public $quote_currency = null;

    public $data_fetch_time = 'M1';

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
     *
     * @return mixed
     */
    public function getData(int $limit = 20) {
        $result = [];

        $oanda = new oanda_rest_api();
        if ($response = $oanda->doApiRequest('candles', [
            'instrument' => $this->getPairName(),
            'granularity' => $this->data_fetch_time,
            'alignmentTimezone' => 'Europe/London',
            'dailyAlignment' => 22,
            'count' => ($limit > 5000 ? 5000 : $limit)
        ], 'GET')
        ) {
            if (!empty($response['candles'])) {
                foreach ($response['candles'] as $row) {
                    if ($row['complete']) {
                        $class = new avg_price_data();
                        $class->pair = $this;
                        $class->start_date_time = date('d/m/Y H:i:s', substr($row['time'], 0, 10));
                        $class->open = $row['openBid'];
                        $class->close = $row['closeBid'];
                        $class->high = $row['highBid'];
                        $class->low = $row['lowBid'];
                        $class->volume = $row['volume'];
                        $class->spread = get::pip_difference($row['closeAsk'], $row['closeBid'], $this);

                        $result[] = $class;
                    }
                }
            }
        }

        return $result;
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