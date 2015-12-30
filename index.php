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

    var margin = {top: 20, right: 20, bottom: 30, left: 50},
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

    var candlestick = techan.plot.candlestick()
        .xScale(x)
        .yScale(y);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left");

    var svg = d3.select("body").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

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
        var accessor = candlestick.accessor();

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
                .call(candlestick);

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

            // Associate the zoom with the scale after a domain has been applied
            zoom.x(x.zoomable().clamp(false)).y(y);
<?php
    echo $end."\n";
?>

    function plotNewData(NewData) {
        var accessor = candlestick.accessor(),
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

        svg.select("g.candlestick").datum(_data).call(candlestick);

        x.domain(_data.map(accessor.d));
        y.domain(techan.scale.plot.ohlc(_data, accessor).domain());
    }

    function draw() {
        svg.select("g.candlestick").call(candlestick);
        svg.select("g.x.axis").call(xAxis);
        svg.select("g.y.axis").call(yAxis)
    }
</script>
</body>
</html>