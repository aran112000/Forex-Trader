<?php
require('inc/bootstrap.php');

$tasks = [
    'fetchRates' => function() {
        pairs::getPricingFeed();
    },
    'trader' => function() {
        $trader = new trader();
        $trader->initRealtimeTrading();
    }
];

new multi_process_manager('Server', $tasks);