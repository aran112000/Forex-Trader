<?php

/**
 * Class oanda_base
 */
abstract class oanda_base {

    const API_VERSION = 1;

    const DATE_TIME_FORMAT = 'UNIX';

    const LIVE_TRADING = false;

    const ACCOUNT_ID = 3801954;
    const API_KEY = '292cc63cd1beb6d1cedd79145aa1e6d6-539708ca87ef48c291bb234e94bbff01';

    protected $demo_endpoint = null;
    protected $live_endpoint = null;

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function getRequestFields(array $fields): array {
        return array_merge([
            'accountId' => self::ACCOUNT_ID,
        ], $fields);
    }

    /**
     * @param string $endpoint
     * @param array  $fields
     * @param string $method
     *
     * @return string
     */
    protected function getEndpoint(string $endpoint, array $fields = [], string $method = 'POST'): string {
        $endpoint = trim($endpoint, '/');
        if (self::LIVE_TRADING) {
            $url = $this->live_endpoint . '/v' . self::API_VERSION . '/' . $endpoint;
        } else {
            $url = $this->demo_endpoint . '/v' . self::API_VERSION . '/' . $endpoint;
        }

        if ($method === 'GET' && !empty($fields)) {
            $url .= (!strstr($endpoint, '?') ? '?' : '&') . http_build_query($fields);
        }

        return $url;
    }

    /**
     * @param int $unix_timestamp
     *
     * @return string
     */
    public function timestampToDate(int $unix_timestamp): string {
        return date('d/m/Y H:i:s', substr($unix_timestamp, 0, 10));
    }

    /**
     * @param int $unix_timestamp
     *
     * @return string
     */
    public function timestampToMysqlDate(int $unix_timestamp): string {
        return date('Y-m-d H:i:s', substr($unix_timestamp, 0, 10));
    }
}