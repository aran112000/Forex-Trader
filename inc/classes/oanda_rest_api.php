<?php

/**
 * Class oanda_rest_api
 */
final class oanda_rest_api extends oanda_base {

    protected $demo_endpoint = 'https://api-fxpractice.oanda.com';
    protected $live_endpoint = 'https://api-fxtrade.oanda.com';

    /**
     * @param string $endpoint
     * @param array  $fields
     * @param string $method
     *
     * @return array
     */
    public function doApiRequest(string $endpoint, array $fields = [], string $method = 'POST'): array {
        $request_fields = $this->getRequestFields($fields);
        $method = strtoupper($method);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->getEndpoint($endpoint, $request_fields, $method),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Accept-Datetime-Format: ' . self::DATE_TIME_FORMAT,
                'Authorization: Bearer ' . self::API_KEY,
            ]
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, count($request_fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_fields);
        }
        if (!$result = curl_exec($ch)) {
            $details = curl_getinfo($ch);
            echo '<p>Request Failed:<br /><pre>' . print_r($details, true) . '</pre></p>';
        }
        curl_close($ch);

        if ($json_result = json_decode($result, true)) {
            return $json_result;
        }

        trigger_error('Invalid response received: ' . $result);
        return [];
    }
}