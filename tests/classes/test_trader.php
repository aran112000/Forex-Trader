<?php

/**
 * Class test_trader
 */
class test_trader {

    const MINIMUM_DATA_POINTS_TO_TEST = 50;
    const MAXIMUM_DATA_POINTS_TO_TEST = (1 * (52 * 5));
    const PAIRS_TO_TEST = 30;

    /**
     *
     */
    public function initTests() {
        $total_candles_inspected = 0;
        $total_entries = 0;
        $total_valid_trades = 0;
        $total_wins = 0;
        $total_losses = 0;
        $start_balance = account::getBalance();

        $pair_i = 0;

        echo '<h1>Starting account balance: &#163;' . number_format($start_balance, 2) . '</h1>' . "\n";

        foreach (pairs::getPairs() as $pair) {
            $pair_i++;

            $pair_start_balance = account::getBalance();

            /**@var _pair $pair*/
            $pair->data_fetch_time = 'D';
            $full_data_set = $pair->getData((self::MAXIMUM_DATA_POINTS_TO_TEST + self::MINIMUM_DATA_POINTS_TO_TEST));

            $candles_inspected = 0;
            $entries = 0;
            $valid_trades = 0;
            $wins = 0;
            $losses = 0;
            $trade_percentage_gain = 0;

            $i = 0;
            foreach ($full_data_set as $key => $row) {
                $i++;
                if ($i >= self::MINIMUM_DATA_POINTS_TO_TEST && $i < (self::MAXIMUM_DATA_POINTS_TO_TEST + self::MINIMUM_DATA_POINTS_TO_TEST)) {
                    $candles_inspected++;
                    $test_data = array_slice($full_data_set, 0, $i);

                    $analysis = new _analysis();
                    $analysis->default_pair_data = $test_data;

                    $latest_day = end($test_data);
                    if (get::date('w', $latest_day->date) == 5) {
                        // Skip placing any trades on a Friday
                        continue;
                    }

                    $results = $analysis->doScorePair($pair);
                    $trade_details = $results['entries'];

                    if (!empty($trade_details)) {
                        $remaining_data = array_slice($full_data_set, ($key + 1), 150);

                        foreach ($trade_details as $trade) {
                            $entries++;
                            if ($results = $this->doVerifyTrade($remaining_data, $trade['entry_details'])) {
                                $valid_trades++;
                                $pip_gain = $results['pip_gain'];

                                if ($pip_gain > 0) {
                                    $wins++;
                                    $trade_percentage_gain += $results['percentage_gain'];
                                } else if ($pip_gain < 0) {
                                    $losses++;
                                    $trade_percentage_gain += $results['percentage_gain'];
                                }

                                account::setBalance(((account::getBalance() / 100) * $results['percentage_gain']) + account::getBalance());
                            }
                        }
                    }
                }
            }

            $pair_end_balance = account::getBalance();
            $pair_gross_profit = ((($pair_end_balance - $pair_start_balance) / $pair_start_balance) * 100);

            if ($valid_trades != 0) {
                $win_percentage = round((($wins / $valid_trades) * 100), 2);
                $loss_percentage = round((($losses / $valid_trades) * 100), 2);
            } else {
                $win_percentage = 0;
                $loss_percentage = 100;
            }

            echo '<div style="display:block;width:16.5%;float:left;margin:.08%;padding:.75%;box-sizing:border-box;border:1px solid #ccc;">';
                echo '<h1 style="margin:0 0 10px 0;">' . $pair->getPairName('/') . ' Results</h1>' . "\n";
                echo '<p style="padding: 0 0 3px 0;margin:0">Daily candles analysed: ' . number_format($candles_inspected) . ' (' . number_format(($candles_inspected / 365), 1) . ' Yrs)</p>' . "\n";
                echo '<p style="padding: 0 0 3px 0;margin:0">Entries identified: ' . $entries . ' (' . round((($entries / $candles_inspected) * 100), 2) . '%)</p>' . "\n";
                echo '<p style="padding: 0 0 3px 0;margin:0">Entries triggered: ' . $valid_trades . ($entries > 0 ? ' (' . round((($valid_trades / $entries) * 100), 2) . '%)' : '') . '</p>' . "\n";
                echo '<p style="padding: 0 0 3px 0;margin:0">Winning trades: ' . $wins . ' (' . $win_percentage . '%)</p>' . "\n";
                echo '<p style="padding: 0 0 3px 0;margin:0">Loosing trades: ' . $losses . ' (' . $loss_percentage . '%)</p>' . "\n";
                echo '<p style="padding: 0 0 3px 0;margin:0">Gross profit: ' . number_format($pair_gross_profit, 2) . '%</p>' . "\n";
            echo '</div>';

            $total_candles_inspected += $candles_inspected;
            $total_entries += $entries;
            $total_valid_trades += $valid_trades;
            $total_wins += $wins;
            $total_losses += $losses;

            if ($pair_i === self::PAIRS_TO_TEST) {
                break;
            }
        }

        $end_balance = account::getBalance();
        $total_gross_profit = ((($end_balance - $start_balance) / $start_balance) * 100);

        echo '<div style="display:block;width:99.7%;clear:both;margin:.15%;padding:.75%;box-sizing:border-box;border:1px solid #ccc;color:red;">';
            echo '<h1 style="color:red;margin:0 0 10px 0;">Results Summary</h1>' . "\n";
            echo '<p style="color:red;padding: 0 0 3px 0;margin:0">Daily candles analysed: ' . number_format($total_candles_inspected) . '</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 3px 0;margin:0">Entries identified: ' . $total_entries . ' (' . round((($total_entries / $total_candles_inspected) * 100), 2) . '%)</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 3px 0;margin:0">Entries triggered: ' . $total_valid_trades . ' (' . round((($total_valid_trades / $total_entries) * 100), 2) . '%)</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 3px 0;margin:0">Winning trades: ' . $total_wins . ' (' . round((($total_wins / $total_valid_trades) * 100), 2) . '%)</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 3px 0;margin:0">Loosing trades: ' . $total_losses . ' (' . round((($total_losses / $total_valid_trades) * 100), 2) . '%)</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 3px 0;margin:0">Gross profit: ' . number_format($total_gross_profit, 2) . '%</p>' . "\n";
        echo '</div>';

        echo '<h1>Closing account balance: &#163;' . number_format($end_balance, 2) . '</h1>' . "\n";
        echo '<h1>Gross profit: &#163;' . number_format($end_balance - $start_balance, 2) . '</h1>' . "\n";
    }

    /**
     * @param array $future_data_set
     * @param array $trade_details
     *
     * @return array|bool
     */
    private function doVerifyTrade(array $future_data_set, array $trade_details) {
        $trading = null;

        foreach ($future_data_set as $future_data) {
            /**@var avg_price_data $future_data */
            if ($trading === null) {
                if ($trade_details['type'] === 'Buy' && $future_data->close >= $trade_details['entry']) {
                    // Buy order was triggered
                    $trading = true;
                } else if ($trade_details['type'] === 'Sell' && $future_data->close <= $trade_details['entry']) {
                    // Sell order was triggered
                    $trading = true;
                } else {
                    // Trade wasn't entered this time :(
                    return false;
                }
            } else {
                // Trade has begun, continue tracking until we hit our stop loss
                if ($trade_details['type'] === 'Buy' && $future_data->low <= $trade_details['stop']) {

                    $gain = ($trade_details['stop'] - $trade_details['entry']) * $trade_details['amount'];

                    // Stop loss hit
                    return [
                        'pip_gain' => get::pipDifference($trade_details['stop'], $trade_details['entry'], $trade_details['pair'], false),
                        'gain' => round($gain, 3),
                        'percentage_gain' => round(($gain / ($trade_details['entry'] * $trade_details['amount']) * 100), 4),
                    ];
                } else if ($trade_details['type'] === 'Sell' && $future_data->high >= $trade_details['stop']) {

                    $gain = ($trade_details['entry'] - $trade_details['stop']) * $trade_details['amount'];

                    // Stop loss hit
                    return [
                        'pip_gain' => get::pipDifference($trade_details['entry'], $trade_details['stop'], $trade_details['pair'], false),
                        'gain' => round($gain, 3),
                        'percentage_gain' => round(($gain / ($trade_details['stop'] * $trade_details['amount']) * 100), 4),
                    ];
                } else {
                    // Still trading nicely, increase our stop loss
                    if ($trade_details['type'] === 'Buy') {
                        $new_stop = $future_data->low - 0.0002;
                        if ($new_stop > $trade_details['stop']) {
                            $trade_details['stop'] = $new_stop;
                        }
                    } else if ($trade_details['type'] === 'Sell') {
                        $new_stop = $future_data->high + 0.0002;
                        if ($new_stop < $trade_details['stop']) {
                            $trade_details['stop'] = $new_stop;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $message
     * @param array  $data
     */
    protected function printResults(string $message, array $data = []) {
        if (defined('cli') && cli) {
            echo $message . "\n";
            if (!empty($data)) {
                echo print_r($data, true) . "\n";
            }
            echo "----------------------------------------------------------------------------------------------------\n";
        } else {
            echo '<div style="float:left;width:32%;height:450px;display:block;box-sizing:border-box;padding:0.5% 0.5% 0 0.5%;margin:0.5%;border:1px solid #ccc;text-align:center">';
            echo '<p style="margin-bottom:0;padding-bottom:0;">' . $message . '</p>' . "\n";
            if (!empty($data)) {
                echo '<p><pre>' . print_r($data, true) . '</pre></p>';
            }
            echo '</div>';
            flush();
        }
    }
}