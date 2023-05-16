define([
    "jquery",
    "logger",
    "chart",
    "mage/mage",
], function (
    $,
    logger,
    Chart,
    mage
) {
    "use strict";
    $(document).ready(function () {
        pieChartStatus();
        returnChart();
        pieChartValidation();
        $("#thedropdown").on('change', function (e) {
            returnChart();
        });
    });
    function returnChart() {
        let getdateCollection = $("#saleschartDate").val();
        var dateArrayCollection = [];
        dateArrayCollection = getdateCollection.split(",");
        let salesCollection = JSON.parse($("#saleschartSale").val());
        let returnCollection = JSON.parse($("#saleschartReturn").val());
        let averageCollection = JSON.parse($("#saleschartAverage").val());
        var canvasId = document.getElementById("saleschart");



        var data1 = {
            "type": "bar",
            "data": {
                "labels": dateArrayCollection,
                "datasets": [{
                    "label": "Sales",
                    "data": salesCollection,
                    "fill": false,
                    "backgroundColor": '#f1d4b3',
                    "borderColor": '#eb5202',
                    "borderWidth": 1,
                    "lineTension": 0.1
                }, {
                    "label": "Return",
                    "data": returnCollection,
                    "fill": false,
                    "backgroundColor": '#f1d4b3',
                    "borderColor": '#eb5202',
                    "borderWidth": 1,
                    "lineTension": 0.1
                }, {
                    "label": "Average",
                    "data": averageCollection,
                    "fill": false,
                    "backgroundColor": '#f1d4b3',
                    "borderColor": '#eb5202',
                    "borderWidth": 1,
                    "lineTension": 0.1
                }]
            },
            "options": {
                "animations": {
                    "tension": {
                        "duration": "1000",
                        "easing": 'linear',
                        "from": 1,
                        "to": "0",
                        "loop": "true"
                    }
                },
                "layout": {
                    "padding": "2",
                },
                "legend": {
                    "position": "bottom",
                },
                "title": {
                    "display": "true",
                    "text": "Sales Chart"
                },
                "scales": {
                    "yAxes": [{
                        "scaleLabel": {
                            "display": "true",
                            "labelString": "Revenue"
                        }
                    }],
                    "xAxes": [{
                        "scaleLabel": {
                            "display": "true",
                            "labelString": "Data"
                        }
                    }]
                }
            }
        };
        Chart.platform.disableCSSInjection = true;
        if (window.prepareChart != undefined)
            window.prepareChart.destroy();
        window.prepareChart = new Chart(canvasId, data1);

    }

    function pieChartValidation() {
        let date = $("#productvalidate").val();
        var datevalue = [];
        datevalue = date.split(",");
        var canvasId2 = document.getElementById("piechartvalidate");
        var data2 = {
            "type": 'pie',
            "data": {
                "labels": ["NotPublished", "UnPublished", "Published"],
                "datasets": [{
                    "backgroundColor": [
                        "#eb5202",
                        "#95a5a6",
                        "#2ecc71",
                    ],
                    "data": datevalue
                }]
            }
        }
        new Chart(canvasId2, data2);
    }

    function pieChartStatus() {
        let date = $("#productstatus").val();
        var datevalue = [];
        datevalue = date.split(",");
        var canvasId3 = document.getElementById("piechart");
        var data3 = {
            "type": 'pie',
            "data": {
                "labels": ["Error", "Validated", "Not-Validated"],
                "datasets": [{
                    "backgroundColor": [
                        "#3498db",
                        "#2ecc71",
                        "#eb5202",
                    ],
                    "data":datevalue
                }]
            }
        }
        new Chart(canvasId3, data3);
    }
});