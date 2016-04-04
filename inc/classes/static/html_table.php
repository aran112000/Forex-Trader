<?php

/**
 * Class html_table
 */
class html_table {

    /**
     * @var string
     */
    public static $empty_table_cell_value = 'N/A';

    /**
     * @param array $rows
     *
     * @return string
     */
    public static function get(array $rows): string {
        if (empty($rows)) {
            return '';
        }

        $headers = self::getSortedTableHeaders($rows);

        $html = '<div class="table-responsive">
<table class="table table-striped table-hover">
<thead>
    <tr>'."\n";

        foreach ($headers as $header) {
            $html .= '<th>' . $header . '</th>'."\n";
        }

        $html .= '
    </tr>
</thead>
<tbody>'."\n";

        foreach ($rows as $row) {
            $html .= '<tr>'."\n";
            foreach ($row as $value) {
                $html .= '<td>' . $value . '</td>'."\n";
            }
            $html .= '</tr>'."\n";
        }

        $html .= '</tbody>
</table>
</div>';

        return $html;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    private static function getSortedTableHeaders(array &$rows): array {
        $max = 0;
        $headers = [];

        // Lets find the array with the most elements (this will be used as the table header)
        foreach ($rows as $row) {
            $current_count = count($row);
            if ($current_count > $max) {
                $max = $current_count;
                $headers = [];
                foreach ($row as $key => $value) {
                    $headers[] = $key;
                }
            }
        }

        // Lets now pad out any shorter rows found
        $normalised_rows = [];
        foreach ($rows as $key => $row) {
            foreach ($headers as $header) {
                $normalised_rows[$key][$header] = (isset($row[$header]) ? $row[$header] : self::$empty_table_cell_value);
            }
        }
        $rows = $normalised_rows;

        return $headers;
    }
}