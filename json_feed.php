<?php
require('inc/bootstrap.php');

$json = [];

$pair = new aud_cad();
if ($rows = $pair->getData(365)) {
    foreach ($rows as $row) {
        $unix_timestamp = get::strtotime_from_uk_format($row->date);

        $json[] = [
            'TimeKey' => floor($unix_timestamp / 86400),
            'Date' => date('d/m/Y H:i:s', $unix_timestamp),
            'Open' => $row->open,
            'Close' => $row->close,
            'High' => $row->high,
            'Low' => $row->low,
            'Volume' => $row->volume,
        ];
    }
}

header('Content-Type: application/json');
die(json_encode($json));