<?php
set_time_limit(0);
require('../inc/bootstrap.php');
/*$tester = new _analysis_test_runner();
$tester->initTests('macd');*/

$tester = new test_trader();
$tester->initTests();