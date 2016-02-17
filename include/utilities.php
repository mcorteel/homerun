<?php
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

/*****************************************************************************
 *                            Debugging functions
 *****************************************************************************/
$_DEBUG = "";

// This function returns the name of the function that called the current
// function (with class name if applicable)
function getCallingFunctionName() {
    $trace = debug_backtrace();
    $caller = $trace[2];
    return (array_key_exists('class', $caller) ? $caller['class'] . "::" : "") . $caller['function'];
}

//This function appends a debug line to the debug console
function debug($text, $level = 0) {
    global $_DEBUG, $ENV_AJAX;
    if($level <= ENV_DEBUG_LEVEL) {
        $_DEBUG .= "<li class=\"debug\"><span class=\"caller\">" . ($ENV_AJAX ? "(ajax) " : "(php) ") . getCallingFunctionName() . "</span> $text</li>"; 
    }
}


//This function appends a warning line to the debug console
function warning($text) {
    global $_DEBUG, $ENV_AJAX;
    $caller = list(, $caller) = debug_backtrace(false);
    $_DEBUG .= "<li class=\"warning\"><span class=\"caller\">" . ($ENV_AJAX ? "(ajax) " : "(php) ") . getCallingFunctionName() . "</span> $text</li>"; 
}


//This function appends an error line to the debug console
function error($text) {
    global $_DEBUG, $ENV_AJAX;
    $caller = list(, $caller) = debug_backtrace(false);
    $_DEBUG .= "<li class=\"error\"><span class=\"caller\">" . ($ENV_AJAX ? "(ajax) " : "(php) ") . getCallingFunctionName() . "</span> $text</li>"; 
}


/*****************************************************************************
 *                   String <-> Array translation functions
 *****************************************************************************/

// This function translates an array to a string (strings can be escaped with
// the optional $escapeChar character (default is single quote escape)
function arrayToString($array, $escapeChar = "'", $concatenationChar = ", ") {
    $ret = "";
    $i = 0;
    foreach($array as $val) {
        if(is_object($val)) {
            if(method_exists($val, "toString")) {
                $val = $val->toString();
            } else {
                warning("cannot convert object to string");
                return $ret;
            }
        }
        if($i++) {
            $ret .= $concatenationChar;
        }
        if(is_string($val)) {
            $ret .= $escapeChar . $val . $escapeChar;
        } else {
            $ret .= $val;
        }
    }
    return $ret;
}


/**
 * This function translates a formatted string to an array. The $separator
 * string can be used to define what separates values (default is comma and
 * space) and if $escapeChar is defined, it will be removed from the beginning
 * and end of each value, if present
 **/
function stringToArray($string, $separator = ", ", $escapeChar = "'") {
    $array = Array();
    while(strlen($string)) {
        if(strpos($string, $separator)) {
            $val = substr($string, 0, strpos($string, $separator));
            if(substr($val, 0, 1) == $escapeChar && substr($val, strlen($val) - 1) == $escapeChar) {
                $array[] = str_replace("\\" . $escapeChar, $escapeChar, substr($val, 1, strlen($val) - 2));
            } else {
                $array[] = $val;
            }
            $string = substr($string, strpos($string, $separator) + strlen($separator));
        } else {
            $array[] = str_replace($escapeChar, "", $string);
            $string = "";
        }
    }
    return $array;
}

/*****************************************************************************
 *                         Money amount functions
 *****************************************************************************/

function toEuros($number, $zeros = false) {
    $number = round(floatval($number), 2);
    $dec = "";
    if(strpos($number, ",") > -1) {
        $dec = substr($number, strpos($number, ",") + 1);
    } elseif($zeros) {
        $number = $number . ",00";
    }
    if(strlen($dec) == 1) {
        $number = $number . "0";
    }
    return "$number&nbsp;€";
}
 
/*****************************************************************************
 *                         Timestamp translation functions
 *****************************************************************************/

function SQLTT($d) {
    return mktime(substr($d, 11, 2), substr($d, 14, 2) , substr($d, 17, 2), substr($d, 5, 2), substr($d, 8, 2), substr($d, 0, 4));
}

function TTSQL($d) {
    return date("Y-m-d H:i:s", $d);
}
 
/**
 * This function returns the complete date (Y-m-d H:i) associated with a
 * timestamp. This is not very readable for user but it is the most precise.
 **/
function timestampToComplete($timestamp) {
    return date("Y-m-d H:i", $timestamp);
}


/**
 * This function returns a fuzzy date associated with a timestamp. This date
 * is only suitable to give the user a quick idea of how long ago this date was
 **/
function timestampToFuzzy($timestamp) {
    $dayTime = (date("G") * 60 + date("i")) * 60 + date("s");
    $weekTime = (date("N") - 1) * 24 * 3600 + $dayTime;
    $monthTime = (date("j") - 1) * 24 * 3600 + $dayTime;
    if($timestamp >= time() - $dayTime) {
        return "aujourd'hui";
    } elseif($timestamp >= time() - $weekTime) {
        return "cette semaine";
    } elseif($timestamp >= time() - $weekTime - 7 * 24) {
        return "la semaine dernière";
    } elseif($timestamp >= time() - $monthTime) {
        return "ce mois-ci";
    } else {
        return "il y a " . round((time() - $timestamp) / (24 * 3600)) . " jours";
    }
}


/**
 * This function returns a fancy date associated with a timestamp. This date
 * is the most readable for users.
 **/
function timestampToFancy($timestamp, $hour = false) {
    $dayTime = (date("G") * 60 + date("i")) * 60 + date("s");
    $weekTime = 6 * 24 * 3600 + $dayTime;
    if($timestamp >= time() - $dayTime) {
        $date =  "aujourd'hui à " . date("H:i", $timestamp);
    } elseif($timestamp >= time() - $dayTime - 24 * 3600) {
        $date = "hier à " . date("H\hi", $timestamp);
    } elseif($timestamp >= time() - $weekTime) {
        $date = strftime("%A", $timestamp) . " à " . date("H\hi", $timestamp);
    } else {
        $n = strftime("%e", $timestamp);
        $date = "le " . $n . ($n == 1 ? "<sup>er</sup>" : "") . strftime(" %B %Y", $timestamp) . ($hour ? date(" H\hi", $timestamp) : "");
    }
    return $date;
}


function timestampToRelative($timestamp) {
    if(time() - $timestamp <= 60) {
        return "il y a " . (time() - $timestamp) . " seconde" . (time() - $timestamp > 1 ? "s" : "");
    } elseif(time() - $timestamp <= 3600) {
        return "il y a " . round((time() - $timestamp) / 60) . " minute" . (round((time() - $timestamp) / 60) > 1 ? "s" : "");
    } elseif(time() - $timestamp <= 3600 * 24) {
        return "il y a " . round((time() - $timestamp) / 3600) . " heure" . (round((time() - $timestamp) / 3600) > 1 ? "s" : "");
    }
    if(round((time() - $timestamp) / 24 / 3600) == 1) {
        return "hier";
    } elseif(time() - $timestamp <= 30 * 24 * 3600) {
        return "il y a " . round((time() - $timestamp) / 24 / 3600) . " jours";
    } elseif(time() - $timestamp <= 365 * 24 * 3600) {
        return "il y a " . (date("Y") == date("Y", $timestamp) ? date("n", time()) - date("n", $timestamp) : date("n", time()) + 12 - date("n", $timestamp)) . " mois";
    } elseif($timestamp == 0) {
        return "probablement jamais";
    } else {
        return "il y a " . (date("Y", time()) - date("Y", $timestamp)) . " an" . ((date("Y", time()) - date("Y", $timestamp)) > 1 ? "s" : "");
    }
}

function timestampToCompact($timestamp) {
    $months = Array("", "Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Aoû", "Sep", "Oct", "Nov", "Déc");
    return date("d", $timestamp) . "&nbsp;" . $months[date("n", $timestamp)] . (date("Y", $timestamp) != date("Y") ? date(" Y", $timestamp) : "");
}

function dateToTimestamp($date) {
    return mktime(0, 0, 0, substr($date, 3, 2), substr($date, 0, 2), substr($date, 8));
}
