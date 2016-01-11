<?php

/**
 * Class _analysis
 */
final class _analysis {

    const SECONDS_BETWEEN_ANALYSIS_PROCESSES = 15;
    const MINIMUM_SCORE_TO_TRADE = 1;

    public $default_pair_data = []; // This will only be non-empty when running tests when it's populated with test data

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
    public function doAnalysePairsRecursive(callable $trade_function) {
        $workers = [];
        foreach (pairs::getPairs() as $pair) {
            /**@var _pair $pair*/
            $workers[] = function() use ($pair, $trade_function) {
                while (true) {
                    $score_details = $this->doAnalysePair($pair);

                    log::write($pair->getPairName() . ' pricing analysis details: ' . print_r($score_details, true), LOG::INFO);
                    if ($this->isEntrySignal($score_details)) {
                        log::write($pair->getPairName() . ' - Signals show we\'re good to trade - Score: ' . $score_details['score'], LOG::INFO);
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

        new multi_process_manager('doAnalysePairs', $workers);
    }

    /**
     * @param callable $trade_function
     */
    public function doAnalysePairs(callable $trade_function) {
        foreach (pairs::getPairs() as $pair) {
            /**@var _pair $pair*/
            $score_details = $this->doAnalysePair($pair);

            log::write($pair->getPairName() . ' pricing analysis details: ' . print_r($score_details, true), LOG::DEBUG);
            if ($this->isEntrySignal($score_details)) {
                log::write($pair->getPairName() . ' - Signals show we\'re good to trade - Score: ' . $score_details['score'], LOG::DEBUG);
                socket::send('analysis_result', [
                    'pair' => $pair->getPairName(),
                    'score' => $score_details['score'],
                    'details' => $score_details,
                ]);

                call_user_func($trade_function, $pair);
            }
        }
    }

    /**
     * @param array $score_details
     *
     * @return bool
     */
    public function isEntrySignal(array $score_details): bool {
        return ($score_details['score']['buy'] >= self::MINIMUM_SCORE_TO_TRADE || $score_details['score']['sell'] >= self::MINIMUM_SCORE_TO_TRADE);
    }

    /**
     * @param \_pair $currency_pair
     *
     * @return array
     */
    public function doAnalysePair(_pair $currency_pair): array {
        $score_details = [
            'pair' => $currency_pair->getPairName(),
            'score' => [
                'buy' => 0,
                'sell' => 0,
            ],
            'details' => []
        ];

        if (!empty($this->analysis_methods)) {
            foreach ($this->analysis_methods as $test_class_name => $class) {
                /**@var _base_analysis $class*/
                $class->setPair($currency_pair);
                $class->setData($this->default_pair_data); // Clear data cache (or populate with test data)
                $trade_details = $class->doAnalyse();

                if (!empty($trade_details)) {
                    $score_details['details'][] = [
                        'name' => ucwords(str_replace('_', ' ', $test_class_name)),
                        'trade_details' => $trade_details,
                    ];
                }
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