<?php
class dashboard_page extends _page {

    /**
     * @return string
     */
    protected function getBody(): string {
        return html_table::get([
            [
                'First name' => 'Aran',
                'Surname' => 'Reeks',
                'D.O.B.' => '26/05/1990',
            ],
            [
                'First name' => 'Aran',
                'D.O.B.' => '26/05/1990',
            ],
            [

            ],
        ]);
    }

    public function getActiveTrades(): string {

    }

    public function getRecentTrades(int $limit = 5): string {

    }
}