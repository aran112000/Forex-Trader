<?php

/**
 * Class account
 */
final class account {

    private $account_name = null;
    private $balance = null;
    private $currency = null;
    private $margin_rate = null;

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
        if ($response = $api->doApiRequest('accounts', [], 'GET')) {
            $found = false;
            foreach ($response['accounts'] as $account) {
                if ($account['accountId'] == oanda_base::ACCOUNT_ID) {
                    $found = true;

                    $this->account_name = $response['accounts'][0]['accountName'];
                    $this->currency = $response['accounts'][0]['accountCurrency'];
                    $this->margin_rate = $response['accounts'][0]['marginRate'];

                    break;
                }
            }

            if (!$found) {
                trigger_error('Failed to fetch account details');
            }
        }

        if ($response = $api->doApiRequest('accounts/' . oanda_base::ACCOUNT_ID . '/transactions', [], 'GET')) {
            // TODO; Confirm the order of results. Is the current balance the first of last element in the returned array?
            $last_transaction = end($response['transactions']);

            $this->balance = $last_transaction['accountBalance'];
        }
    }
}