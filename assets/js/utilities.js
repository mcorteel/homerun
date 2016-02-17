/*****************************************************************************
 * Copyright 2013-2016 Maxime Corteel                                        *
 *                                                                           *
 * This file is part of Homerun                                              *
 *                                                                           *
 * Home Helper is free software: you can redistribute it and/or              *
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


var ENV_DEV = true;
var dummyId = 0;


$(document).ready(function(){
    //Set debug window hotkey
    if(ENV_DEV) {
        $("body").on("keydown", function(e){
            if(e.keyCode == 68 && e.ctrlKey) {
                e.preventDefault();
                $("#modal-debug").toggleClass("active");
            }
        });
    }
    
    if(navigator.mozApps) {
        var request = navigator.mozApps.checkInstalled("http://home.dianeetmax.fr/homerun.webapp");
        request.onsuccess = function(){
            if(request.result)
            {
                $(".install-action").hide();
            }
        };
    } else {
        $(".install-action").hide();
    }
    
    $(".header-menu .menu").click(function(){
        $("html, body").animate({ scrollTop: 0 }, "fast");
        $("#navbar").slideToggle();
    });
    
    if($(".header-menu .menu").size()) {
        $("body").addClass("menu-contained");
    }
    
    $("input:first").focus();
    $("input[autofocus]").focus();
    
    $("#modal-debug .close").click(toggleDebug);
});

function toggleDebug() {
    $("#modal-debug").toggleClass("active");
}

function toggleModal(name) {
    $("#modal-" + name).modal("toggle");
    return false;
}


//Disable a button when a field is empty
function disableOnEmpty(source, target) {
    $(source).keyup(function(){
        debug($(this).val());
        $(target).prop("disabled", !$(this).val().length);
    });
}


/**
 * Debug functions
 **/

function debug(text) {
    window.console && console.log(text);
    $("#modal-debug ul").append("<li class=\"debug\"><span class=\"caller\" title=\"" + arguments.callee.caller.name +  "\">(js) " + arguments.callee.caller.name +  "</span> " + text + "</li>");
}


function ajaxDebug(data) {
    if(data.debug && data.debug != "") {
        $("#modal-debug ul").append(data.debug);
    }
}


/**
 * Visual information functions (info, error, warning)
 **/


var hideMainInfoTimeout;
function setHideMainInfo(time) {
    if(time === undefined) {
        var time = 4000;
    }
    window.clearTimeout(hideMainInfoTimeout);
    if(!$("#main-info").is(":visible")) {
        $("#main-info").fadeIn();
    }
    $("#main-info").click(function(){$(this).fadeOut();});
    hideMainInfoTimeout = setTimeout(function(){$("#main-info").fadeOut();}, time);
}


function error(text, time) {
    $("#main-info").removeClass();
    $("#main-info").addClass("alert");
    $("#main-info").addClass("alert-danger");
    $("#main-info").html("<strong>Erreur</strong> " + text);
    setHideMainInfo(time);
}


function info(text, time) {
    $("#main-info").removeClass();
    $("#main-info").addClass("alert");
    $("#main-info").addClass("alert-info");
    $("#main-info").html("" + text);
    setHideMainInfo(time);
}


function warning(text, time) {
    $("#main-info").removeClass();
    $("#main-info").addClass("alert");
    $("#main-info").addClass("alert-warning");
    $("#main-info").html("<strong>Warning</strong> " + text);
    setHideMainInfo(time);
}


function arrayToString(array, escapeChar) {
    if(escapeChar === undefined) {
        var escapeChar = "'";
    }
    var ret = "";
    var k = 0;
    for(var i in array) {
        var val = array[i];
        if(k++) {
            ret += ", ";
        }
        if(typeof val == "string") {
            ret += escapeChar + val + escapeChar;
        } else {
            ret += val;
        }
    }
    return ret;
}


function stringToArray(string, separator, escapeChar) {
    if(separator === undefined) {
        var separator = ", ";
    }
    if(escapeChar === undefined) {
        var escapeChar = "'";
    }
    array = Array();
    while(string.length) {
        if(string.indexOf(separator) > -1) {
            val = string.substr(0, string.indexOf(separator));
            if(val.substr(0, 1) == escapeChar && val.substr(val.length - 1) == escapeChar) {
                array.push(val.substr(1, val.length - 2).replace("\\" . escapeChar, escapeChar));
            } else {
                array.push(val);
            }
            string = string.substr(string.indexOf(separator) + separator.length);
        } else {
            array.push(string.replace(escapeChar, ""));
            string = "";
        }
    }
    return array;
}

function frenchPlural(pattern, n) {
    switch(n) {
        case 0:
            return pattern.replace(/%dfn/g, "d'aucune").replace(/%dn/g, "d'aucun").replace(/%fn/g, "Aucune").replace(/%n/g, "Aucun").replace(/%s/g, "").replace(/%x/g, "au").replace(/%lx/g, "el").replace(/%ls/g, "elle");
            break;
        case 1:
            return pattern.replace(/%dfn/g, "d'une").replace(/%dn/g, "d'un").replace(/%f?n/g, n).replace(/%s/g, "").replace(/%x/g, "au").replace(/%lx/g, "el").replace(/%ls/g, "elle");
            break;
        default:
            return pattern.replace(/%df?n/g, "de " + n).replace(/%f?n/g, n).replace(/%s/g, "s").replace(/%x/g, "aux").replace(/%lx/g, "aux").replace(/%ls/g, "elles");
            break;
    }
}

function toEuros(v, opt_html) {
    var html = opt_html || false;
    return (v+"").replace(".", ",") + (html ? "&nbsp;" : " ") + "€";
}

function installApp() {
    var request = navigator.mozApps.install("http://home.dianeetmax.fr/homerun.webapp");
    request.onsuccess = function(){
        $(".install").hide();
        $(".delete").show();
    };
    request.onerror = function(){
        //Install failed
    };
}
