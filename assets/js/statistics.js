/*****************************************************************************
 * Copyright 2013-2016 Maxime Corteel                                        *
 *                                                                           *
 * This file is part of Homerun                                              *
 *                                                                           *
 * Homerun is free software: you can redistribute it and/or                  *
 * modify it under the terms of the GNU Affero General Public License as     *
 * published by the Free Software Foundation, either version 3 of the        *
 * License, or (at your option) any later version.                           *
 *                                                                           *
 * This program is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             *
 * GNU Affero General Public License for more details.                       *
 *                                                                           *
 * You should have received a copy of the GNU Affero General Public License  *
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.     *
 *****************************************************************************/

var plot = null;
var ds = {datasets: null, labels: null};
var savedOrder = null;
var ctx;
var currentGraphType = "";

function displayGraph() {
    var d = {
        account: $(".account").val(),
        sMonth: $(".bDate").datepicker('getDate').getMonth() + 1,
        sYear: $(".bDate").datepicker('getDate').getFullYear(),
        eMonth: $(".eDate").datepicker('getDate').getMonth() + 1,
        eYear: $(".eDate").datepicker('getDate').getFullYear(),
        graphType: ($(".graphType label.active").size() ? $(".graphType label.active input").val() : "")
    };
    if(savedOrder !== null) {
        d.order = savedOrder;
    }
    $.post("ajax/statistics.php", d, function(data){
        if(data.graphType != currentGraphType) {
            debug("Graph reset");
            $("#diagramContainer").html('<canvas id="diagram" height="170"></canvas>');
            plot = null;
            currentGraphType = data.graphType;
            $(".graphType input[value=" + data.graphType + "]").prop("checked", true).parent().addClass("active");
        }
        ajaxDebug(data);
        $(".options-series").empty();
        //Clear table
        $("#table thead tr:first").html("<th rowspan=2></th>");
        $("#table thead tr:last").empty();
        $("#table tbody").empty();
        //Data plot
        var datasets = [];
        for(var i in data.datasets) {
            var dataset = data.datasets[i];
            if(dataset.display) {
                if(currentGraphType == 'bar') {
                    datasets.unshift(dataset);
                } else {
                    datasets.push(dataset);   
                }
                //Table
                var t = "";
                var m = 0;
                for(var j = 0 ; j < dataset.data.length ; j++) {
                    t += "<td>" + toEuros(Math.round(dataset.data[j]), true) + "</td>";
                    m += parseInt(dataset.data[j]);
                }
                $("#table tbody").append("<tr><th>" + dataset.label + "</th>" + t + "<td>" + toEuros(Math.round(m / dataset.data.length)) + "</td></tr>");
            }
            $(".options-series").append("<li data-id=\"" + dataset.id + "\"" + (dataset.display ? "" : " class=\"disabled\"") + "><span class=\"series-color\" style=\"background-color:" + dataset.backgroundColor + ";border:1px solid " + dataset.borderColor + ";\"></span> <span class=\"series-label\">" + dataset.label + "</span><span class=\"pull-right\"><i class=\"fa fa-caret-up\"></i> <i class=\"fa fa-caret-down\"></i></span></li>");
        }
        var m = 0;
        var t = "";
        var y = "";
        var yL = 0;
        for(var i = 0 ; i < data.labels.length ; i++) {
            var s = 0;
            var d = "";
            for(var j in data.datasets) {
                s += parseInt(data.datasets[j].data[i]);
                d += (d == "" ? "" : " + ") + data.datasets[j].data[i] + "(" + data.datasets[j].label + ")";
            }
            t += "<td>" + toEuros(s, true) + "</td>";
            m += s;
            var month = data.labels[i].substr(0, data.labels[i].length - 5);
            var year = data.labels[i].substr(-4, 4);
            if((y != "" && y!= year) || i == data.labels.length - 1) {
                $("#table thead tr:first").append("<th colspan=" + (yL + (i == data.labels.length - 1 ? 1 : 0))+ ">" + y + "</th>");
                yL = 0;
            }
            y = data.labels[i].substr(-4, 4);
            yL++;
            $("#table thead tr:last").append("<th>" + month + "</th>");
        }
        m = Math.round(m / data.labels.length);
        $("#table tbody").append("<tr><th><em>Total</em></th>" + t + "<td>" + toEuros(m, true) + "</td></tr>");
        //Show over and under mean value
        $("#table tbody tr").each(function(){
            m = parseInt($(this).find("td:last").text());
            $(this).find("td").each(function(){
                var v = parseInt($(this).text());
                if(v > m)
                    $(this).addClass("over");
                if(v < m)
                    $(this).addClass("under");
                if(v == m)
                    $(this).addClass("mean");
            });
        });

        $("#table thead tr:last").append("<th>Moyenne</th>");
        $(".options-series i").css("visibility", "visible");
        $(".options-series i:first, .options-series li:not(.disabled) i:last, .options-series li.disabled i").css("visibility", "hidden");
        ctx = document.getElementById("diagram").getContext("2d");
        if(plot === null) {
            var graph_options = {
                type: 'bar',
                data: {
                    datasets: datasets,
                    labels: data.labels
                },
                options: {
                    scales: {
                        yAxes: [{
                            stacked: true,
                            min: 0
                        }],
                        xAxes: [{
                            stacked: true
                        }],
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enable: true,
                        mode: 'label',
                        position: 'top',
                        cornerRadius: 2,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex].label + ": " + toEuros(tooltipItem.yLabel);
                            }
                        }
                    }
                }
            }
            switch(currentGraphType) {
                case "bar":
                    graph_options.type = 'bar';
                    break;
                case "line":
                    graph_options.type = 'line';
                    for(var i in graph_options.data.datasets) {
                        graph_options.data.datasets[i].fill = false;
                    }
                    graph_options.options.scales = {};
                    break;
                case "area":
                    graph_options.type = 'line';
                    for(var i in graph_options.data.datasets) {
                        graph_options.data.datasets[i].fill = true;
                    }
                    break;
            }
            
            plot = new Chart(ctx, graph_options);
        } else {
            if(currentGraphType == 'line') {
                for(var i in datasets) {
                    datasets[i].fill = false;
                }
            }
            plot.data.datasets = datasets;
            plot.data.labels = data.labels;
            plot.update();
        }
    });
}

function afterLegendModification()
{
    $(".options-series i").css("visibility", "visible");
    $(".options-series i:first, .options-series i:last").css("visibility", "hidden");
    var order = {}, i = 1;
    $(".options-series li").each(function(){
        if($(this).hasClass("disabled")) {
            order[parseInt($(this).data("id"))] = -1 * i++;
        } else {
            order[parseInt($(this).data("id"))] = i++;
        }
    });
    console.log(order);
    savedOrder = order;
    displayGraph();
}

var bDate, eDate;

$(document).ready(function(){
    $(".bDate").datepicker({format: "MM yyyy", weekStart: 1, minViewMode: 1, language: 'fr'});
    $(".eDate").datepicker({format: "MM yyyy", weekStart: 1, minViewMode: 1, language: 'fr'});
    var aYearAgo = new Date();
    aYearAgo.setFullYear(aYearAgo.getFullYear() - 1);
    $(".bDate").datepicker('setDate', aYearAgo);
    $(".eDate").datepicker('setDate', new Date());
    displayGraph();
    $(".account").change(function(){displayGraph();});
    
    $(".graphType input").change(displayGraph);
    
    $(".replot").click(displayGraph);
    
    $(".options-series").on("click", "i.fa-caret-up", function(){
        $(this).closest("li").insertBefore($(this).closest("li").prev("li"));
        afterLegendModification();
    });
    $(".options-series").on("click", "i.fa-caret-down", function(){
        $(this).closest("li").insertAfter($(this).closest("li").next("li"));
        afterLegendModification();
    });
    $(".options-series").on("click", ".series-color", function(){
        $(this).closest("li").toggleClass("disabled");
        afterLegendModification();
    });
    $("input:focus").blur();
});
