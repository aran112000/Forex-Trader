<?php

namespace page;

/**
 * Class trade_history
 * @package page
 */
class trade_history extends _page {

    /**
     * @return string
     */
    protected function getBody(): string {
        return '<h2>Trade History</h2>
        ' . \html_table::get([
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
        ]);
    }
}