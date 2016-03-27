<?php
require('../inc/bootstrap.php');

/*
 * Run at 22:00 Tuesday, Wednesday, Thursday & Friday
 * */
define('testing', true);
account::setBalance(49226.37);

$trader = new trader();
$trader->initSwingTradeAlerts();

echo 'Completed.';