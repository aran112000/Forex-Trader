<?php
require('inc/bootstrap.php');

$tasks = [
    'fetchRates' => function() {
        pairs::getPricing();
    },
    /*'trader' => function() {
        $trader = new trader();
        $trader->initRealtimeTrading();
    }*/
];

new multi_process_manager('Server', $tasks);