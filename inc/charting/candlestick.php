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
                'TimeKey' => $row->timekey,
                'Date' => date('d/m/Y H:i:s', strtotime($row->entry_time)),
                'Open' => (float) $row->open,
                'High' => (float) $row->high,
                'Low' => (float) $row->low,
                'Close' => (float) $row->close,
                'Volume' => (int) $row->volume
            ];
        }

        return '<iframe style="width:100%" height="250" src="/index.php?data=' . urlencode(json_encode($clean_data)) . '" frameborder="none"></iframe>';
    }
}