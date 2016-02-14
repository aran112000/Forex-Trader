<?php
/**
 * Class account
 */
final class account {

    private static $account_name = null;
    private static $balance = null;
    private static $currency = null;
    private static $margin_rate = null;
    private static $open_trades = null;
    private static $open_orders = null;

    /**
     * @return float
     */
    public static function getBalance(): float {
        if (self::$balance === null) {
            self::setAccountDetails();
        }

        return self::$balance;
    }

    /**
     *
     */
    private static function setAccountDetails() {
        $api = new oanda_rest_api();
        if ($response = $api->doApiRequest('accounts/' . oanda_base::ACCOUNT_ID, [], 'GET')) {
            self::$account_name = $response['accountName'];
            self::$currency = $response['accountCurrency'];
            self::$margin_rate = $response['marginRate'];
            self::$balance = $response['balance'];
            self::$open_trades = $response['openTrades'];
            self::$open_orders = $response['openOrders'];
        } else {
            trigger_error('Failed to fetch account details');
        }
    }
}