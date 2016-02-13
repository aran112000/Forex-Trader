<?php
require('../inc/bootstrap.php');

/*
 * Run at 22:00 Tuesday, Wednesday, Thursday & Friday
 * */
define('testing', true);
$trader = new trader();
$trader->initSwingTradeAlerts();