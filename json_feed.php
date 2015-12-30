<?php
require('inc/bootstrap.php');

$wheres = ['pair=\'AUD_CAD\''];
if (isset($_REQUEST['pids'])) {
    $wheres[] = 'pid IN(' . implode(',', $_REQUEST['pids']) . ')';
}

if ($res = db::query('SELECT * FROM 1_minute_view WHERE ' . implode(' AND ', $wheres) . ' ORDER BY timekey ASC')) {
    $json = [];
    if (db::num($res)) {
        while ($row = db::fetch($res)) {
            $json[] = [
                'TimeKey' => floor((strtotime($row['entry_time']) / 60)),
                'Date' => date('d/m/Y H:i:s', strtotime($row['entry_time'])),
                'Open' => round($row['entry_price'], 5),
                'Close' => round($row['exit_price'], 5),
                'High' => round($row['max_price'], 5),
                'Low' => round($row['min_price'], 5),
                'Volume' => (int) $row['volume'],
            ];
        }
    }

    header('Content-Type: application/json');
    die(json_encode($json));
}