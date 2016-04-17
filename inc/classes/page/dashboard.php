<?php

namespace page;

/**
 * Class dashboard
 * @package page
 */
class dashboard extends _page {

    public function __construct() {
        self::setJsFiles('/js/push.js');
    }

    /**
     * @return string
     */
    protected function getBody(): string {
        return '<h1>Dashboard</h1>
        <p class="lead">Welcome back ' . \user::get()['first_name'] . ' - ' . $this->getNotificationSubscribeButton() . '</p>
        <div class="row">
            <div class="col-md-6">' . self::getActiveTrades() . '</div>
            <div class="col-md-6">' . self::getRecentTrades() . '</div>
        </div>';
    }

    protected function getNotificationSubscribeButton() {
        return '<button class="js-push-button" onclick="subscribe()">Subscribe</button>';
    }

    /**
     * @return string
     */
    public function getActiveTrades(): string {
        return '<h2>Active Trades</h2>
        ' . \html_table::get([
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
        ]);
    }

    /**
     * @param int $limit
     *
     * @return string
     */
    public function getRecentTrades(int $limit = 5): string {
        return '<h2>Recent Trades</h2>
        ' . \html_table::get([
            ['Trade ID' => 12345, 'Closed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Closed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Closed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Closed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Closed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Closed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
        ]);
    }
}