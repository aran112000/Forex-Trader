<?php

/**
 * Class test_ema_50
 */
class test_ema_50 extends _analysis_test_case {

    /**
     * @var null|ema_20
     */
    protected $analysis_class = null;

    /**
     * @var int
     */
    protected $sample_date_size = 10080; // 7 days

    /**
     * @return mixed
     */
    public function initTest() {
        $test_data = $this->getSampleData();
        $this->analysis_class->setData($test_data);
        $score = $this->analysis_class->doAnalyse();

        // TODO
        if ($this->assertTradeableScore($score)) {
            if (!cli) {
                $this->printResults('50 EMA Line Convergence');
            } else {
                $this->printResults('Trade signal found!', $test_data);
            }
        }
    }
}