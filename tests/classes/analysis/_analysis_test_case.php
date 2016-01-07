<?php

/**
 * Class _analysis_test_case
 */
abstract class _analysis_test_case {

    /**
     * @var null|_pair
     */
    protected $pair = null;

    /**
     * @var null|_base_analysis
     */
    protected $analysis_class = null;

    /**
     * @var null|array
     */
    private $data = null;

    /**
     * @var string
     */
    protected $test_pair = 'aud_cad'; // AUD_CAD has some of the highest volumes (30/12/2015)

    /**
     * @var int
     */
    protected $sample_date_size = 15000;

    /**
     * _analysis_test_case constructor.
     */
    public function __construct() {
        $this->setTestPair();
        $this->setSampleData();
        $this->setAnalysisClass();
    }

    /**
     * @return mixed
     */
    abstract function initTest();

    /**
     * Was $score sufficient to result in a trade if this was the only signal?
     *
     * @param float $score
     *
     * @return bool
     */
    protected function assertTradeableScore(float $score): bool {
        return ($score >= 0.5);
    }

    /**
     *
     */
    private function setAnalysisClass() {
        $class_name = str_replace('test_', '', get_called_class());
        $this->analysis_class = new $class_name();
        $this->analysis_class->setTest(true);
        $this->analysis_class->setPair($this->pair);
    }

    /**
     *
     */
    private function setTestPair() {
        /**@var _pair $pair */
        $this->pair = new $this->test_pair();
    }

    /**
     *
     */
    private function setSampleData() {
        if ($this->data === null) {
            $this->data = $this->pair->getData($this->sample_date_size, 'ASC');
        }
    }

    /**
     * @return array
     */
    protected function getSampleData(): array {
        $this->setSampleData();

        return $this->data;
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
            echo '<div style="float:left;width:32%;display:block;box-sizing:border-box;padding:0.5% 0.5% 0 0.5%;margin:0.5%;border:1px solid #ccc;text-align:center">';
            echo '<p style="margin-bottom:0;padding-bottom:0;">' . $message . '</p>' . "\n";
            if (!empty($data)) {
                echo '<p><pre>' . print_r($data, true) . '</pre></p>';
            }
            echo '</div>';
            flush();
        }
    }
}