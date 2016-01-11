<?php

/**
 * Class test_trader
 */
class test_trader {

    const MINIMUM_DATA_POINTS_TO_TEST = 50;
    const MAXIMUM_DATA_POINTS_TO_TEST = 1000;

    /**
     *
     */
    public function initTests() {
        $aud_cad_class = new aud_cad();
        $aud_cad_class->data_fetch_time = '1d';
        $full_data_set = $aud_cad_class->getData((self::MAXIMUM_DATA_POINTS_TO_TEST + self::MINIMUM_DATA_POINTS_TO_TEST));

        $trades_inspected = 0;
        $signals = 0;
        $valid_trades = 0;

        $i = 0;
        foreach ($full_data_set as $key => $row) {
            $i++;
            if ($i >= self::MINIMUM_DATA_POINTS_TO_TEST && $i <= self::MAXIMUM_DATA_POINTS_TO_TEST) {
                $trades_inspected++;
                $test_data = array_slice($full_data_set, 0, $i);

                $analysis = new _analysis();
                $analysis->default_pair_data = $test_data;
                $results = $analysis->doAnalysePair($aud_cad_class);
                $trade_details = $results['details'];

                if (!empty($trade_details)) {
                    $signals++;
                    $remaining_data = array_slice($full_data_set, ($key + 1), 150);

                    $pip_difference = $this->doVerifyTrade($remaining_data, $trade_details[0]['trade_details']);
                    if ($pip_difference > 0) {
                        echo '<p>PIP: ' . $pip_difference . '</p>'."\n";
                        $valid_trades++;
                    }
                }
            } else if ($i > self::MAXIMUM_DATA_POINTS_TO_TEST) {
                break;
            }
        }

        echo '<h1>Results</h1>'."\n";
        echo '<p>Total: ' . $trades_inspected . '</p>'."\n";
        echo '<p>Signals: ' . $signals . '</p>'."\n";
        echo '<p>Valid trades: ' . $valid_trades . '</p>'."\n";
        echo '<p>Success percentage: ' . round((($valid_trades / $signals) * 100), 2) . '%</p>'."\n";
    }

    /**
     * @param array $future_data
     * @param array $trade_details
     *
     * @return float
     */
    private function doVerifyTrade(array $future_data, array $trade_details): float {
        $entry_price = 0;

        foreach ($future_data as $key => $data) {
            /**@var avg_price_data $data */
            if ($entry_price === 0) {
                if ($trade_details['type'] == 'Buy' && $data->close >= $trade_details['entry']) {
                    // Buy order was triggered
                    $entry_price = $trade_details['entry'];
                } else if ($trade_details['type'] == 'Sell' && $data->close <= $trade_details['entry']) {
                    // Sell order was triggered
                    $entry_price = $trade_details['entry'];
                } else {
                    // Trade wasn't entered this time :(
                    return 0;
                }
            } else {
                // Trade has begun, continue tracking until we hit our stop loss
                if ($trade_details['type'] == 'Buy' && $data->low <= $trade_details['exit']) {
                    // Stop loss hit
                    return get::pip_difference($data->low, $entry_price);
                } else if ($trade_details['type'] == 'Sell' && $data->low >= $trade_details['exit']) {
                    // Stop loss hit
                    return get::pip_difference($trade_details['entry'], $entry_price);
                } else {
                    // Still trading nicely, increase our stop loss
                    $position_size = abs($trade_details['entry'] - $trade_details['exit']);

                    // Move our stop loss
                    if ($trade_details['type'] == 'Buy') {
                        $trade_details['exit'] = $data->close - $position_size;
                    } else if ($trade_details['type'] == 'Sell') {
                        $trade_details['exit'] = $data->close + $position_size;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @param array $score_details
     *
     * @return string
     */
    private function getScoreInformation(array $score_details): string {
        $details = '';
        $score_lines = [];
        foreach ($score_details['details'] as $row) {
            if ($row['score']['buy'] > 0) {
                $score_lines[] = $row['name'] . ' (buy): ' . round($row['score']['buy'], 2);
            }
            if ($row['score']['sell'] > 0) {
                $score_lines[] = $row['name'] . ' (sell): ' . round($row['score']['sell'], 2);
            }
        }

        return $details . '<br />' . implode(', ', $score_lines);
    }

    /**
     * @param string $message
     * @param array  $data
     */
    protected function printResults(string $message, array $data = []) {
        if (cli) {
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