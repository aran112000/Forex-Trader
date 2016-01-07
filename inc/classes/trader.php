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
        $analysis->doAnalysePairsRecursive(function($pair) {
            /*$trade = new trade($pair);
            $trade->doBuy();
            $trade->doSell();*/
        });
    }
}