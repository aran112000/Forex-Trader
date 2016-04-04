<?php
class dashboard_page extends _page {

    /**
     * @return string
     */
    protected function getBody(): string {
        return '<h1>Dashboard</h1>
        <p class="lead">Welcome back ' . user::get()['first_name'] . '</p>
        <div class="row">
            <div class="col-md-6">' . self::getActiveTrades() . '</div>
            <div class="col-md-6">' . self::getRecentTrades() . '</div>
        </div>';
    }

    public function getActiveTrades(): string {
        return '<h2>Active Trades</h2>
        ' . html_table::get([
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
        ]);
    }

    public function getRecentTrades(int $limit = 5): string {
        return '<h2>Recent Trades</h2>
        ' . html_table::get([
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345679, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Status' => 'Active', 'Profit' => '<span class="text-success">£147.55</span>'],
        ]);
    }
}