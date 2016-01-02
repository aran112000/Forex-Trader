<?php

/**
 * Class trader
 */
final class trader {

    /**
     *
     */
    public function initRealtimeTrading() {
        $analysis = new _analysis();
        $analysis->doAnalysePairs(function($pair) {
            /*$trade = new trade($pair);
            $trade->doBuy();
            $trade->doSell();*/
        });
    }
}