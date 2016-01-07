<?php

/**
 * Class test_trader
 */
class test_trader {

    const MINIMUM_DATA_POINTS_TO_TEST = 75;
    const MAXIMUM_DATA_POINTS_TO_TEST = 1000;

    /**
     *
     */
    public function initTests() {
        $candlestick = new candlestick();
        $aud_cad_class = new aud_cad();

        $full_data_set = $aud_cad_class->getData((self::MAXIMUM_DATA_POINTS_TO_TEST + self::MINIMUM_DATA_POINTS_TO_TEST));
        $rows = count($full_data_set);

        for ($i = 1; $i <= $rows; $i++) {
            if ($i >= self::MINIMUM_DATA_POINTS_TO_TEST && $i <= self::MAXIMUM_DATA_POINTS_TO_TEST) {
                $test_data = array_slice($full_data_set, 0, $i);

                $analysis = new _analysis();
                $analysis->default_pair_data = $test_data;
                $score_details = $analysis->doAnalysePair($aud_cad_class);
                if ($analysis->isEntrySignal($score_details)) {
                    $latest_data = array_splice($test_data, -2);
                    $iframe_data = array_merge($latest_data, [$full_data_set[$i], $full_data_set[($i + 1)]]);

                    $message = 'Predicted reversal <small>(Last 2 were after the signal)</small>';
                    if ($this->doVerifyReversal($latest_data[1], $full_data_set[$i])) {
                        $message .= '<br /><strong>Valid reversal identified</strong>';
                        $iframe_data = array_merge($latest_data, [
                            $full_data_set[$i],
                            $full_data_set[($i + 1)],
                            $full_data_set[($i + 2)],
                            $full_data_set[($i + 3)],
                            $full_data_set[($i + 4)],
                            $full_data_set[($i + 5)],
                            $full_data_set[($i + 6)],
                            $full_data_set[($i + 7)],
                            $full_data_set[($i + 8)],
                            $full_data_set[($i + 9)],
                            $full_data_set[($i + 10)],
                        ]);

                        $this->printResults($message . $candlestick->getIframeChart($iframe_data) . $this->getScoreInformation($score_details));
                    }
                }
            } else if ($i > self::MAXIMUM_DATA_POINTS_TO_TEST) {
                break;
            }
        }
    }

    /**
     * @param avg_price_data $before_data
     * @param avg_price_data $after_data
     *
     * @return bool
     */
    private function doVerifyReversal(avg_price_data $before_data, avg_price_data $after_data): bool {
        return ($before_data->getDirection() !== $after_data->getDirection());
    }

    /**
     * @param array $score_details
     *
     * @return string
     */
    private function getScoreInformation(array $score_details): string {
        $details = 'Buy score: ' . round($score_details['score']['buy'], 2) . ', Sell score: ' . round($score_details['score']['sell'], 2);
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