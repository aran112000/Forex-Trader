<?php
require('inc/bootstrap.php');
$account = new account();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/css/TechanJS/TechanJS.css"/>
    <title>Forex trader</title>
</head>
<body>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script src="http://techanjs.org/techan.min.js"></script>
<?php
if (!isset($_REQUEST['data'])) {
?>
<h1>AUD/CAD</h1>
<h2>Current account balance: &#163;<?= number_format($account->getBalance(), 4) ?></h2>
<script src="http://192.168.1.95:8081/socket.io/socket.io.js"></script>
<script>
    var socket = io('http://192.168.1.95:8081');
    socket.on('connect', function () {
        socket.on('price', function (data) {
            if (data.pair == 'AUD_CAD') {
                console.log(data);
                plotNewData(data);
            }
        });
        socket.on('analysis_result', function (data) {
            if (data.pair == 'AUD_CAD') {
                console.error(data);
            }
        });
    });
</script>
<?php } ?>

<script>

    var margin = {top: 20, right: 60, bottom: 30, left: 50},
        width = (window.innerWidth - <?=(isset($_REQUEST['data']) ? 10 : 10)?>) - margin.left - margin.right,
        height = (window.innerHeight - <?=(isset($_REQUEST['data']) ? 20 : 130)?>) - margin.top - margin.bottom;

    var parseDate = d3.time.format("%d/%m/%Y %H:%M:%S").parse,
        timeFormat = d3.time.format('%H:%M:%S'),
        valueFormat = d3.format(',.5fs');

    var zoom = d3.behavior.zoom()
        .on("zoom", draw);

    var x = techan.scale.financetime()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    var ema0 = techan.plot.ema()
        .xScale(x)
        .yScale(y);

    var ema0Calculator = techan.indicator.ema()
        .period(20);

    var ema1 = techan.plot.ema()
        .xScale(x)
        .yScale(y);

    var ema1Calculator = techan.indicator.ema()
        .period(50);

    var candlestick = techan.plot.candlestick()
        .xScale(x)
        .yScale(y);

    var ohlc = techan.plot.ohlc()
        .xScale(x)
        .yScale(y);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom");

    var xTopAxis = d3.svg.axis()
        .scale(x)
        .orient("top");

    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left");

    var yRightAxis = d3.svg.axis()
        .scale(y)
        .orient("right");

    var svg = d3.select("body").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var coordsText = svg.append('text')
        .style("text-anchor", "end")
        .attr("class", "coords")
        .attr("x", width - 5)
        .attr("y", 15);

    svg.append("rect")
        .attr("class", "pane")
        .attr("width", width)
        .attr("height", height)
        .call(zoom);

    svg.append("clipPath")
        .attr("id", "clip")
        .append("rect")
        .attr("x", 0)
        .attr("y", y(1))
        .attr("width", width)
        .attr("height", y(0) - y(1));

    var ohlcAnnotation = techan.plot.axisannotation()
        .axis(yAxis)
        .format(d3.format(',.2fs'));

    var ohlcRightAnnotation = techan.plot.axisannotation()
        .axis(yRightAxis)
        .translate([width, 0]);

    var timeAnnotation = techan.plot.axisannotation()
        .axis(xAxis)
        .format(d3.time.format('%Y-%m-%d'))
        .width(65)
        .translate([0, height]);

    var timeTopAnnotation = techan.plot.axisannotation()
        .axis(xTopAxis);

    var crosshair = techan.plot.crosshair()
        .xScale(x)
        .yScale(y)
        .xAnnotation([timeAnnotation, timeTopAnnotation])
        .yAnnotation([ohlcAnnotation, ohlcRightAnnotation])
        .on("enter", enter)
        .on("out", out)
        .on("move", move);

    var ohlcSelection = svg.append("g")
        .attr("class", "ohlc")
        .attr("transform", "translate(0,0)");

    ohlcSelection.append("g")
        .attr("class", "indicator ema ma-0")
        .attr("clip-path", "url(#ohlcClip)");

    ohlcSelection.append("g")
        .attr("class", "indicator ema ma-1")
        .attr("clip-path", "url(#ohlcClip)");

    var _data = [];
<?php
if (!isset($_REQUEST['data'])) {
$end = '});';
?>
    d3.json("/json_feed.php", function (error, data) {
<?php
} else {
    $end = '};';
?>
    init(<?=urldecode($_REQUEST['data'])?>);
    function init(data) {
<?php } ?>
        var accessor = ohlc.accessor();

        _data = data.slice(-150).map(function (d) {
            return {
                timekey: d.TimeKey,
                date: parseDate(d.Date),
                open: +d.Open,
                high: +d.High,
                low: +d.Low,
                close: +d.Close,
                volume: +d.Volume
            };
        }).sort(function (a, b) {
            return d3.ascending(accessor.d(a), accessor.d(b));
        });

            x.domain(_data.map(accessor.d));
            y.domain(techan.scale.plot.ohlc(_data, accessor).domain());

            svg.append("g")
                .datum(_data)
                .attr("class", "candlestick")
                .attr("clip-path", "url(#clip)")
                .call(ohlc);

            svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis);

            svg.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("y", 6)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                .text("Price");

            svg.append('g')
                .attr("class", "crosshair")
                .call(crosshair);

            svg.append("g")
                .attr("class", "y annotation right")
                .datum([_data[(_data.length - 1)].close])
                .call(ohlcRightAnnotation);

            svg.append("g")
                .attr("class", "y axis")
                .attr("transform", "translate(" + width + ",0)")
                .call(yRightAxis);

            svg.select("g.ema.ma-0").datum(ema0Calculator(_data));
            svg.select("g.ema.ma-1").datum(ema1Calculator(_data));

            // Associate the zoom with the scale after a domain has been applied
            zoom.x(x.zoomable().clamp(false)).y(y);
<?php
    echo $end."\n";
?>

    function plotNewData(NewData) {
        var accessor = ohlc.accessor(),
            key = _data.length - 1;

        if (typeof _data[key] != 'undefined' && _data[key].timekey === NewData.timekey) {
            _data[key].volume++;
            _data[key].close = +NewData.bid;
            if (NewData.bid > _data[key].high) {
                _data[key].high = +NewData.bid;
            } else if (NewData.bid < _data[key].low) {
                _data[key].low = +NewData.bid;
            }
        } else {
            _data.push({
                timekey: NewData.timekey,
                date: parseDate(NewData.date),
                open: +NewData.bid,
                high: +NewData.bid,
                low: +NewData.bid,
                close: +NewData.bid,
                volume: 1
            });
        }

        svg.select("g.candlestick").datum(_data).call(ohlc);

        svg.select("g.annotation.right").datum([_data[(_data.length - 1)].close]).call(ohlcRightAnnotation);
        //svg.select("g.annotation.right").datum([_data[(_data.length - 1)]]);

        refreshIndicator(svg.select("g.ema.ma-0"), ema0, ema0Calculator(_data));
        refreshIndicator(svg.select("g.ema.ma-1"), ema1, ema1Calculator(_data));

        x.domain(_data.map(accessor.d));
        y.domain(techan.scale.plot.ohlc(_data, accessor).domain());
    }

    function refreshIndicator(selection, indicator, data) {
        var datum = selection.datum();
        // Some trickery to remove old and insert new without changing array reference,
        // so no need to update __data__ in the DOM
        datum.splice.apply(datum, [0, datum.length].concat(data));
        selection.call(indicator);
    }

    function draw() {
        svg.select("g.candlestick").call(ohlc);
        svg.select("g.x.axis").call(xAxis);
        svg.select("g.y.axis").call(yAxis)
    }

    function enter() {
        coordsText.style("display", "inline");
    }

    function out() {
        coordsText.style("display", "none");
    }

    function move(coords) {
        coordsText.text(
            timeAnnotation.format()(coords[0]) + ", " + ohlcAnnotation.format()(coords[1])
        );
    }
</script>
</body>
</html>