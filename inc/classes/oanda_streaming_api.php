<?php

/**
 * Class oanda_streaming_api
 */
final class oanda_streaming_api extends oanda_base {

    protected $demo_endpoint = 'https://stream-fxpractice.oanda.com';
    protected $live_endpoint = 'https://stream-fxtrade.oanda.com';

    /**
     * @param string   $endpoint
     * @param array    $fields
     * @param string   $method
     * @param callable $callback_function
     *
     * @return mixed
     */
    public function doApiRequest(string $endpoint, array $fields = [], string $method = 'POST', callable $callback_function): bool {
        $request_fields = $this->getRequestFields($fields);
        $method = strtoupper($method);

        $url = $this->getEndpoint($endpoint, $request_fields, $method);

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        $port = substr($url, 0, 5) == 'https' ? 443 : 80;

        if (!$fp = fsockopen($scheme == 'https' ? 'ssl://' . $host : $host, $port)) {
            trigger_error('Failed to establish a connection to the upstream.');
            return false;
        }

        fputs($fp, $this->getHead($path, $host, $query));
        $output = '';
        while (!feof($fp)) {
            $response = fgets($fp, 4096);
            $output .= $response;
            if (strstr($response, "\r\n")) {

                $json_response = json_decode($output, true);
                if (is_array($json_response)) {
                    if (isset($json_response['tick'])) {
                        call_user_func($callback_function, $json_response['tick']);
                    } else if (isset($json_response['code'])) {
                        echo 'API Request Error: ' . print_r($json_response, true) . "\n";
                        log::write('Streaming API Error: ' . print_r($json_response, true), log::ERROR);

                        return false;
                    }
                }

                $output = '';
            }
        }
        fclose($fp);

        return true;
    }

    /**
     * @param string $path
     * @param string $host
     * @param string $query
     *
     * @return string
     */
    private function getHead(string $path, string $host, string $query): string {
        return 'GET ' . $path . (!empty($query) ? '?' . $query : '') . ' HTTP/1.1'."\r\n"
            . 'Host: ' . $host . "\r\n"
            . 'Content-Type: application/x-www-form-urlencoded' . "\r\n"
            . 'X-Accept-Datetime-Format: ' . self::DATE_TIME_FORMAT . "\r\n"
            . 'Authorization: Bearer ' . self::API_KEY."\r\n"
            . 'Connection: close' . "\r\n\r\n";
    }
}