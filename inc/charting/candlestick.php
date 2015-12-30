<?php

/**
 * Class candlestick
 */
class candlestick extends base_chart {

    /**
     * @param array $data
     *
     * @return string
     */
    public function getIframeChart(array $data): string {
        $clean_data = [];
        foreach ($data as $row) {
            $clean_data[] = [
                'TimeKey' => floor(strtotime($row['entry_time']) / 60),
                'Date' => date('d/m/Y H:i:s', strtotime($row['entry_time'])),
                'Open' => (float) $row['entry_price'],
                'High' => (float) $row['max_price'],
                'Low' => (float) $row['min_price'],
                'Close' => (float) $row['exit_price'],
                'Volume' => (int) $row['volume']
            ];
        }

        return '<iframe style="width:100%" height="250" src="/index.php?data=' . urlencode(json_encode($clean_data)) . '" frameborder="none"></iframe>';
    }
}