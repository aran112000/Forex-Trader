<?php
require('inc/bootstrap.php');

if ($res = db::query('SELECT * FROM pricing_1d WHERE pair=\'AUD_CAD\' ORDER BY timekey ASC')) {
    $json = [];
    if (db::num($res)) {
        while ($row = db::fetch($res)) {
            $json[] = [
                'TimeKey' => $row['timekey'],
                'Date' => date('d/m/Y H:i:s', strtotime($row['entry_time'])),
                'Open' => round($row['open'], 5),
                'Close' => round($row['close'], 5),
                'High' => round($row['high'], 5),
                'Low' => round($row['low'], 5),
                'Volume' => (int) $row['volume'],
            ];
        }
    }

    header('Content-Type: application/json');
    die(json_encode($json));
}