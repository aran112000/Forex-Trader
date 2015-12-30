<?php

/**
 * Class test_high_low_test
 */
class test_high_low_test extends _analysis_test_case {

    /**
     * @var null|high_low_test
     */
    protected $analysis_class = null;

    /**
     * @return mixed
     */
    public function initTest() {
        $test_batches = array_chunk($this->getSampleData(), 3);
        $candlestick = new candlestick();

        foreach ($test_batches as $test_data) {
            $this->analysis_class->setData($test_data);
            $score = $this->analysis_class->doAnalyse();
            if ($this->assertTradeableScore($score)) {
                if (!cli) {
                    $this->printResults('Predicted reversal' . $candlestick->getIframeChart($test_data));
                } else {
                    $this->printResults('Trade signal found!', $test_data);
                }
            }
        }
    }
}