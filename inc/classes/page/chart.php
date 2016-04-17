<?php

namespace page;

class chart extends _page {

    public function __construct() {
        self::setJsFiles('/libs/trading_view/charting_library/charting_library.min.js');
        self::setJsFiles('/libs/trading_view/charting_library/datafeed/udf/datafeed.js');
    }

    /**
     * @return string
     */
    protected function getBody(): string {
        $this->setInlineJs('TradingView.onready(function()
			{
				var widget = new TradingView.widget({
					fullscreen: true,
					symbol: \'AA\',
					interval: \'D\',
					container_id: "tv_chart_container",
					//	BEWARE: no trailing slash is expected in feed URL
					datafeed: new Datafeeds.UDFCompatibleDatafeed("http://demo_feed.tradingview.com"),
					library_path: "/libs/trading_view/charting_library/",
					locale: "en",
					//	Regression Trend-related functionality is not implemented yet, so it\'s hidden for a while
					drawings_access: { type: \'black\', tools: [ { name: "Regression Trend" } ] },
					disabled_features: ["use_localstorage_for_settings"],
					preset: "mobile"
				});
			})');

        return '<div id="tv_chart_container"></div>';
    }
}