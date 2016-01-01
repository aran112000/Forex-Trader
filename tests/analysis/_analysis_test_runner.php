<?php

/**
 * Class _analysis_test_runner
 */
class _analysis_test_runner {

    private $test_cases = null;

    /**
     * _analysis_test_runner constructor.
     */
    public function __construct() {
        $this->setTestClasses();
    }

    /**
     * @param string $specific_test
     */
    public function initTests(string $specific_test = '') {
        if ($specific_test !== '') {
            if (isset($this->test_cases[$specific_test])) {
                $class = $this->test_cases[$specific_test];#
                /**@var _analysis_test_case $class*/
                $class->initTest();
            } else {
                throw new InvalidArgumentException('This is not a valid test case');
            }
        } else {
            foreach ($this->test_cases as $class) {
                /**@var _analysis_test_case $class */
                $class->initTest();
            }
        }
    }

    /**
     *
     */
    private function setTestClasses() {
        if ($this->test_cases === null) {
            foreach (glob(__DIR__ . '/*.php') as $file) {
                $filename = basename($file, '.php');
                if (substr($filename, 0, 1) != '_') {
                    /**@var _analysis_test_case $class */
                    $this->test_cases[str_replace('test_', '', $filename)] = new $filename();
                }
            }
        }
    }
}