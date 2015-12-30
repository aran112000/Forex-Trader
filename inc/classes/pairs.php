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
     * Oanda only permit 4 instruments per `prices` request, as such we'll batch the requests into a number of workers
     * each processing up to 4 instruments
     *
     * @return array
     */
    protected static function getPairsBatched(): array {
        $pairs = [];
        foreach (self::getPairs() as $pair => $pair_object) {
            /**@var _pair $pair_object*/
            $pairs[$pair] = $pair_object->base_currency . '_' . $pair_object->quote_currency;
        }
        $pairs = array_chunk($pairs, 5);

        return $pairs;
    }

    /**
     *
     */
    public static function getPricing() {
        $workers = [];
        $oanda = new oanda_streaming_api();
        foreach (self::getPairsBatched() as $batch) {
            $workers[] = function() use ($oanda, $batch) {
                $oanda->doApiRequest('prices', ['instruments' => implode(',', $batch)], 'GET', function (array $response) use ($oanda) {
                    socket::send('price', [
                        'timekey' => floor((substr($response['time'], 0, 10) / 60)),
                        'date' => $oanda->timestampToDate($response['time']),
                        'pair' => $response['instrument'],
                        'bid' => $response['bid'],
                        'ask' => $response['ask']
                    ]);

                    db::query('INSERT DELAYED INTO pricing SET pair=\'' . db::esc($response['instrument']) . '\', ts=\'' . $oanda->timestampToMysqlDate($response['time']) . '\', bid=\'' . db::esc($response['bid']) . '\', ask=\'' . db::esc($response['ask']) . '\'');
                });
            };
        }

        new multi_process_manager('getPricing', $workers);
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