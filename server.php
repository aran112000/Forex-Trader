<?php
require('inc/bootstrap.php');

$tasks = [
    'fetch_live_rates' => function() {
        pairs::getPricingFeed();
    },
    /*'day_trader' => function() {
        $trader = new trader();
        $trader->initRealtimeTrading();
    },*/
];

new multi_process_manager('Server', $tasks);