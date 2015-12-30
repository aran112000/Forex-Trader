<?php

/**
 * Class _analysis
 */
final class _analysis {

    const SECONDS_BETWEEN_ANALYSIS_PROCESSES = 60;
    const MINIMUM_SCORE_TO_TRADE = 0.6;

    private $analysis_methods = null;

    /**
     * analysis constructor.
     */
    public function __construct() {
        $this->setAnalysisMethods();
    }

    /**
     *
     */
    public function doAnalysePairs() {
        $workers = [];
        foreach (pairs::getPairs() as  $pair) {
            /**@var _pair $pair*/
            $workers[] = function() use ($pair) {
                while (true) {
                    $score = $this->doTestPair($pair);

                    if ($score >= self::MINIMUM_SCORE_TO_TRADE) {
                        socket::send('analysis_result', [
                            'pair' => $pair->getPairName(),
                            'score' => $score,
                            'message' => 'This looks good... Lets trade!'
                        ]);
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
    private function doTestPair(_pair $currency_pair): float {
        $score = 0;
        $total_tests = count($this->analysis_methods);
        foreach ($this->analysis_methods as $class) {
            /**@var _base_analysis $class*/
            $class->setPair($currency_pair);
            $score += $class->doAnalyse();
        }

        // Return a combined score
        return ($score / $total_tests);
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