<?php
require('../../inc/bootstrap.php');

$limit = 500;

// Populate pricing_1d table
if ($res = db::query('SELECT pair FROM pricing_1d GROUP BY pair')) {
    $oanda = new oanda_rest_api();
    while ($row = db::fetch($res)) {
        for ($batch = 0; $batch < 50; $batch++) {

            if ($response = $oanda->doApiRequest('candles', ['instrument' => $row['pair'], 'granularity' => 'D', 'end' => strtotime('-' . ($limit * $batch) . ' days'), 'count' => $limit], 'GET')) {

                /*echo '<p><pre>' . print_r($response, true) . '</pre></p>';
                die();*/

                foreach ($response['candles'] as $row2) {
                    if ($row2['complete'] == true) {
                        $row2['time'] = substr($row2['time'], 0, 10);

                        db::query('INSERT DELAYED INTO pricing_1d SET
                        timekey=' . floor($row2['time'] / 86400) . ',
                        pair=\'' . db::esc($row['pair']) . '\',
                        entry_time=\'' . date('Y-m-d H:i:s', $row2['time']) . '\',
                        exit_time=\'' . date('Y-m-d H:i:s', $row2['time']) . '\',
                        open=\'' . $row2['openBid'] . '\',
                        close=\'' . $row2['closeBid'] . '\',
                        high=\'' . $row2['highBid'] . '\',
                        low=\'' . $row2['lowBid'] . '\',
                        volume=\'' . $row2['volume'] . '\'
                    ');
                    }
                }
            }
        }
    }
}
