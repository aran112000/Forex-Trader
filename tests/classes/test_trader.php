<?php

/**
 * Class test_trader
 */
class test_trader {

    const MAXIMUM_DATA_POINTS_TO_TEST = 4500;
    const PAIRS_TO_TEST = 30;

    /**
     *
     */
    public function initTests() {
        $total_trades_inspected = 0;
        $total_signals = 0;
        $total_valid_trades = 0;
        $total_wins = 0;
        $total_losses = 0;
        $total_percentage_gain = 0;

        $pair_i = 0;
        foreach (pairs::getPairs() as $pair) {
            $pair_i++;

            /**@var _pair $pair*/
            $pair->data_fetch_time = 'D';
            $full_data_set = $pair->getData((self::MAXIMUM_DATA_POINTS_TO_TEST));

            $candles_inspected = 0;
            $entries = 0;
            $valid_trades = 0;
            $wins = 0;
            $losses = 0;
            $gain = 0;
            $percentage_gain = 0;

            $i = 0;
            foreach ($full_data_set as $key => $row) {
                $i++;
                $candles_inspected++;
                $test_data = array_slice($full_data_set, 0, $i);

                $analysis = new _analysis();
                $analysis->default_pair_data = $test_data;
                $results = $analysis->doScorePair($pair);
                $trade_details = $results['entries'];

                if (!empty($trade_details)) {
                    $remaining_data = array_slice($full_data_set, ($key + 1), 150);

                    foreach ($trade_details as $trade) {
                        $entries++;
                        if ($results = $this->doVerifyTrade($remaining_data, $trade['entry_details'])) {
                            $pip_difference = $results['pip_gain'];

                            if ($pip_difference != 0) {
                                $valid_trades++;

                                if ($pip_difference > 0) {
                                    $wins++;
                                    $percentage_gain += $results['percentage_gain'];
                                    $gain += $results['gain'];
                                } else if ($pip_difference < 0) {
                                    $losses++;
                                    $percentage_gain += $results['percentage_gain'];
                                    $gain += $results['gain'];
                                }
                            }
                        }
                    }
                }
            }

            echo '<div style="display:block;width:16.5%;float:left;margin:.08%;padding:.75%;box-sizing:border-box;border:1px solid #ccc;">';
                echo '<h1 style="margin:0 0 15px 0;">' . $pair->getPairName('/') . ' Results</h1>' . "\n";
                echo '<p style="padding: 0 0 5px 0;margin:0">Daily candles analysed: ' . number_format($candles_inspected) . ' (' . number_format(($candles_inspected / 365), 1) . ' Yrs)</p>' . "\n";
                echo '<p style="padding: 0 0 5px 0;margin:0">Trades entries identified: ' . $entries . '</p>' . "\n";
                echo '<p style="padding: 0 0 5px 0;margin:0">Entries triggered: ' . $valid_trades . ' (' . round((($valid_trades / $entries) * 100), 2) . '%)</p>' . "\n";
                echo '<p style="padding: 0 0 5px 0;margin:0">Winning trades: ' . $wins . ' (' . round((($wins / $valid_trades) * 100), 2) . '%)</p>' . "\n";
                echo '<p style="padding: 0 0 5px 0;margin:0">Loosing trades: ' . $losses . '</p>' . "\n";
                echo '<p style="padding: 0 0 5px 0;margin:0">Percentage gain: ' . number_format($percentage_gain, 2) . '%</p>' . "\n";
            echo '</div>';

            $total_trades_inspected += $candles_inspected;
            $total_signals += $entries;
            $total_valid_trades += $valid_trades;
            $total_wins += $wins;
            $total_losses += $losses;
            $total_percentage_gain += $percentage_gain;

            if ($pair_i === self::PAIRS_TO_TEST) {
                break;
            }
        }

        echo '<div style="display:block;width:99.7%;float:left;margin:.15%;padding:.75%;box-sizing:border-box;border:1px solid #ccc;color:red;">';
            echo '<h1 style="color:red;margin:0 0 15px 0;">Results Summary</h1>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total days analysed: ' . number_format($total_trades_inspected) . '</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total Trades placed: ' . number_format($total_signals) . '</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total Trades triggered: ' . number_format($total_valid_trades) . '</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total Entry trigger percentage: ' . round((($total_valid_trades / $total_signals) * 100), 2) . '%</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total winning trades: ' . number_format($total_wins) . '</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total loosing trades: ' . number_format($total_losses) . '</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total Winning trade percentage: ' . round((($total_wins / $total_valid_trades) * 100), 2) . '%</p>' . "\n";
            echo '<p style="color:red;padding: 0 0 5px 0;margin:0">Total percentage gain: ' . number_format($total_percentage_gain, 2) . '%</p>' . "\n";
        echo '</div>';
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

                    $gain = ($trade_details['stop'] * $trade_details['amount']) - ($trade_details['entry'] * $trade_details['amount']);

                    // Stop loss hit
                    return [
                        'pip_gain' => get::pip_difference($trade_details['stop'], $trade_details['entry'], false),
                        'gain' => round($gain, 3),
                        'percentage_gain' => round(($gain / ($trade_details['entry'] * $trade_details['amount']) * 100), 4),
                    ];
                } else if ($trade_details['type'] === 'Sell' && $future_data->high >= $trade_details['stop']) {

                    $gain = ($trade_details['entry'] * $trade_details['amount']) - ($trade_details['stop'] * $trade_details['amount']);

                    // Stop loss hit
                    return [
                        'pip_gain' => get::pip_difference($trade_details['entry'], $trade_details['stop'], false),
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