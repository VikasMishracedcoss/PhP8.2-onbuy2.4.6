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
        returnChart();
        $("#dropdownPeriod").on('change', function (e) {
            returnChart();
        });
    });
    function returnChart() {
        let getdateCollection = $("#datescollection").val();
        dateArrayCollection = [];
        var dateArrayCollection = getdateCollection.split(",");
        let salesCollection = JSON.parse($("#salescollection").val());
        let retrnCollection = JSON.parse($("#returncollection").val());
        var canvasId = document.getElementById("graph");
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
                    "data": retrnCollection,
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
        if (window.prepareChart != undefined)
            window.prepareChart.destroy();
        window.prepareChart = new Chart(canvasId, data1);
    }
});