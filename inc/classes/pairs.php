<?php

/**
 * Class pairs
 */
class pairs {

    /**
     * @var null|array
     */
    private static $pairs = null;

    /**
     * @return array
     */
    protected static function getPairNames(): array {
        $pairs = [];
        foreach (self::getPairs() as $pair => $pair_object) {
            /**@var _pair $pair_object */
            $pairs[$pair] = $pair_object->base_currency . '_' . $pair_object->quote_currency;
        }

        return $pairs;
    }

    /**
     *
     */
    public static function getPricingFeed() {
        while (true) {
            self::getPricing();
        }
    }

    /**
     *
     */
    public static function getPricing() {
        $oanda = new oanda_streaming_api();
        $oanda->doApiRequest('prices', ['instruments' => implode(',', self::getPairNames())], 'GET', function (array $response) use ($oanda) {
            $response['time'] = substr($response['time'], 0, 10);

            // Daily
            socket::send('price', [
                'timekey' => floor($response['time'] / 86400),
                'date' => $oanda->timestampToDate($response['time']),
                'pair' => $response['instrument'],
                'bid' => $response['bid'],
                'ask' => $response['ask']
            ]);

            db::query('INSERT DELAYED INTO pricing SET pair=\'' . db::esc($response['instrument']) . '\', ts=\'' . $oanda->timestampToMysqlDate($response['time']) . '\', bid=\'' . db::esc($response['bid']) . '\', ask=\'' . db::esc($response['ask']) . '\'');

            db::query('INSERT DELAYED INTO pricing_1m SET
                  timekey=' . floor($response['time'] / 60) . ',
                  pair=\'' . db::esc($response['instrument']) . '\',
                  entry_time=\'' . date('Y-m-d H:i:s', $response['time']) . '\',
                  exit_time=\'' . date('Y-m-d H:i:s', $response['time']) . '\',
                  open=\'' . $response['bid'] . '\',
                  close=\'' . $response['bid'] . '\',
                  high=\'' . $response['bid'] . '\',
                  low=\'' . $response['bid'] . '\',
                  volume=1
              ON DUPLICATE KEY UPDATE
                  exit_time=VALUES(exit_time),
                  high=IF (high < VALUES(high), VALUES(high), high),
                  low=IF (low > VALUES(low), VALUES(low), low),
                  volume=(volume+VALUES(volume))
              ');
            db::query('INSERT DELAYED INTO pricing_1d SET
                  timekey=' . floor($response['time'] / 86400) . ',
                  pair=\'' . db::esc($response['instrument']) . '\',
                  entry_time=\'' . date('Y-m-d H:i:s', $response['time']) . '\',
                  exit_time=\'' . date('Y-m-d H:i:s', $response['time']) . '\',
                  open=\'' . $response['bid'] . '\',
                  close=\'' . $response['bid'] . '\',
                  high=\'' . $response['bid'] . '\',
                  low=\'' . $response['bid'] . '\',
                  volume=1
              ON DUPLICATE KEY UPDATE
                  exit_time=VALUES(exit_time),
                  high=IF (high < VALUES(high), VALUES(high), high),
                  low=IF (low > VALUES(low), VALUES(low), low),
                  volume=(volume+VALUES(volume))
              ');
        });
    }

    /**
     * @return mixed
     */
    public static function getPairs(): array {
        if (self::$pairs === null) {
            self::setPairs();
        }

        return self::$pairs;
    }

    /**
     * @return int
     */
    public static function getNumberOfPairs(): int {
        return count(self::getPairs());
    }

    /**
     *
     */
    private static function setPairs() {
        foreach (glob(root . '/inc/classes/pairs/*.php') as $file) {
            if (!strstr($file, '_pair.php')) {
                $class_name = basename($file, '.php');
                /**@var _pair $class*/
                $class = new $class_name();
                if ($class->isEnabled()) {
                    self::$pairs[$class_name] = $class;
                }
            }
        }
    }
}