<?php
require('../../inc/bootstrap.php');

// Populate pricing_1d table
if ($res = db::query('SELECT pair FROM pricing_1d GROUP BY pair')) {
    $oanda = new oanda_rest_api();
    while ($row = db::fetch($res)) {
        if ($response = $oanda->doApiRequest('candles', ['instrument' => $row['pair'], 'granularity' => 'D', 'count' => 500], 'GET')) {
            if (isset($response['candles'])) {
                foreach ($response['candles'] as $row2) {
                    if ($row2['complete'] == true) {
                        $row2['time'] = substr($row2['time'], 0, 10);

                        bulk_db::add_query('pricing_1d', [
                            'timekey' => floor($row2['time'] / 86400),
                            'pair' => $row['pair'],
                            'entry_time' => date('Y-m-d H:i:s', $row2['time']),
                            'exit_time' => date('Y-m-d H:i:s', $row2['time']),
                            'open' => $row2['openBid'],
                            'close' => $row2['closeBid'],
                            'high' => $row2['highBid'],
                            'low' => $row2['lowBid'],
                            'volume' => $row2['volume'],
                        ]);
                    }
                }
            }
        }
    }
}

bulk_db::do_process_queries();