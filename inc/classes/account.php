<?php

/**
 * Class account
 */
final class account {

    private $account_name = null;
    private $balance = null;
    private $currency = null;
    private $margin_rate = null;
    private $open_trades = null;
    private $open_orders = null;

    /**
     * @return float
     */
    public function getBalance(): float {
        if ($this->balance === null) {
            $this->setAccountDetails();
        }

        return $this->balance;
    }

    /**
     *
     */
    private function setAccountDetails() {
        $api = new oanda_rest_api();
        if ($response = $api->doApiRequest('accounts/' . oanda_base::ACCOUNT_ID, [], 'GET')) {
            $this->account_name = $response['accountName'];
            $this->currency = $response['accountCurrency'];
            $this->margin_rate = $response['marginRate'];
            $this->balance = $response['balance'];
            $this->open_trades = $response['openTrades'];
            $this->open_orders = $response['openOrders'];
        } else {
            trigger_error('Failed to fetch account details');
        }
    }
}