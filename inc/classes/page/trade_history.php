<?php

namespace page;

/**
 * Class trade_history
 * @package page
 */
class trade_history extends _page {

    /**
     * @return string
     */
    protected function getBody(): string {
        return '<h2>Trade History</h2>
        ' . \html_table::get([
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£491.53</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-danger">-£4.51</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
            ['Trade ID' => 12345, 'Placed' => '01/04/2016 16:12', 'Pair' => 'GBP/USD', 'Direction' => 'Long', 'Profit' => '<span class="text-success">£147.55</span>'],
        ]) . '
        
        <nav class="text-center">
          <ul class="pagination">
            <li>
              <a href="#" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>
            <li><a href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">4</a></li>
            <li><a href="#">5</a></li>
            <li>
              <a href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>';
    }
}