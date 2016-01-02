<?php

/**
 * Class _analysis
 */
final class _analysis {

    const SECONDS_BETWEEN_ANALYSIS_PROCESSES = 1;
    const MINIMUM_SCORE_TO_TRADE = 1.90;

    private $analysis_methods = null;

    /**
     * analysis constructor.
     */
    public function __construct() {
        $this->setAnalysisMethods();
    }

    /**
     * @param callable $trade_function
     */
    public function doAnalysePairs(callable $trade_function) {
        $workers = [];
        foreach (pairs::getPairs() as  $pair) {
            /**@var _pair $pair*/
            $workers[] = function() use ($pair, $trade_function) {
                while (true) {
                    $score_details = $this->doTestPair($pair);

                    log::write($pair->getPairName() . ' pricing analysis score: ' . $score_details['score'] . ' - Details: ' . print_r($score_details, true), LOG::DEBUG);
                    if ($score_details['score'] >= self::MINIMUM_SCORE_TO_TRADE) {
                        log::write($pair->getPairName() . ' - Signals show we\'re good to trade - Score: ' . $score_details['score'], LOG::DEBUG);
                        socket::send('analysis_result', [
                            'pair' => $pair->getPairName(),
                            'score' => $score_details['score'],
                            'details' => $score_details,
                        ]);

                        call_user_func($trade_function, $pair);
                    }

                    sleep(self::SECONDS_BETWEEN_ANALYSIS_PROCESSES);
                }
            };
        }

        // Initialise our workers
        new multi_process_manager('doAnalysePairs', $workers);
    }

    /**
     * @param \_pair $currency_pair
     *
     * @return float
     */
    private function doTestPair(_pair $currency_pair): array {
        $score_details = [
            'score' => 0,
            'details' => []
        ];
        foreach ($this->analysis_methods as $test_class_name => $class) {
            /**@var _base_analysis $class*/
            $class->setPair($currency_pair);
            $class->setData([]); // Clear data cache
            $score = $class->doAnalyse();
            $score_details['details'][] = [
                'name' => ucwords(str_replace('_', ' ', $test_class_name)),
                'score' => $score,
            ];
            if ($class->signal_strength == 'minor') {
                $score_details['score'] += ($score / 10);
            } else {
                $score_details['score'] += $score;
            }
        }

        // Return a combined score
        return $score_details;
    }

    /**
     *
     */
    private function setAnalysisMethods() {
        if ($this->analysis_methods === null) {
            foreach (glob(__DIR__ . '/*.php') as $file) {
                $filename = basename($file, '.php');
                if (substr($filename, 0, 1) != '_') {
                    /**@var _base_analysis $class*/
                    $class = new $filename();
                    if ($class->isEnabled()) {
                        $this->analysis_methods[$filename] = $class;
                    }
                }
            }
        }
    }
}