<?php

/**
 * Class test_macd
 */
class test_macd extends _analysis_test_case {

    /**
     * @var null|macd
     */
    protected $analysis_class = null;

    /**
     * @var int
     */
    protected $sample_date_size = 150;

    /**
     * @return mixed
     */
    public function initTest() {
        // TODO
        $score = $this->analysis_class->doAnalyse();
        echo '<p>' . $score . '</p>'."\n";

        /*$test_batches = array_chunk($this->getSampleData(), 3);
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
        }*/
    }
}