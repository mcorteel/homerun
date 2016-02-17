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
 *****************************************************************************
 * This file contains useful additions to the date object and a partial copy
 * of PHP's date function
 *****************************************************************************/

// This function returns number with a zero in front if 0 <= number <= 9 
function dateAddZero(number) {
    if(number >= 0 && number < 10)
        return "0" + number;
    return number;
}


// This function, added to the date object returns the week number
Date.prototype.getWeek = function() {
    // Copy date so don't modify original
    d = new Date(this);
    d.setHours(0,0,0);
    // Set to nearest Thursday: current date + 4 - current day number
    // Make Sunday's day number 7
    d.setDate(d.getDate() + 4 - (d.getDay()||7));
    // Get first day of year
    var yearStart = new Date(d.getFullYear(),0,1);
    // Calculate full weeks to nearest Thursday
    var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7)
    if(weekNo > 0) {
        return weekNo;
    } else {
        return 52 + weekNo;
    }
}


// This functions determines the month (0-11) of the given week
function getMonthFromWeek(week) {
    if(week < 0) {
        return 11;
    }
    if(week > 53) {
        return 1;
    }
    var yearStart = new Date((new Date()).getFullYear(), 0, 1);
    var i = 0;
    while(yearStart.getDay() != 4) {
        yearStart = new Date((new Date()).getFullYear(),0, ++i);
    }
    //yearStart is now the first thursday of the year
    //Go through each week until we get to this week's thursday
    while(yearStart.getWeek() != week && yearStart.getYearsDay() > 1) {
        yearStart.setTime(yearStart.getTime() + 1000 * 60 * 60 * 24 * 7);
    }
    yearStart.setTime(yearStart.getTime() + 1000 * 60 * 60 * 24 * 7);
    //Return the month of this week's thursday
    return yearStart.getMonth();
}


// This function, added to the date object returns the number of the day in the current year
Date.prototype.getYearsDay = function() {
    var onejan = new Date(this.getFullYear(),0,1);
    return Math.ceil(((this.getTime() - onejan.getTime()) / 86400000));
}


// Get the suffix that goes after the day number (in the month)
Date.prototype.getSuffix = function() {
    switch(this.getDate()) {
        case 1:
            return "st";
        case 2:
            return "nd";
        case 3:
            return "rd";
        default:
            return "th";
    }
}


// Get the day (word)
Date.prototype.getDaysName = function(shortened) {
    if(typeof shortened == "undefined" || shortened == false) {
        var week = Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
    } else {
        var week = Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
    }
    return week[this.getDay()];
}


// Get the month (word)
Date.prototype.getMonthsName = function(shortened) {
    if(typeof shortened == "undefined" || shortened == false) {
        var year = Array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
    } else {
        var year = Array("Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Aoû", "Sep", "Oct", "Nov", "Déc");
    }
    return year[this.getMonth()];
}


// This function copies (partially) the PHP date() function
function date(string, timestamp) {
    if(typeof timestamp == "undefined") {
        var date = new Date();
    }
    if(typeof timestamp == "Object") {
        var date = timestamp;
    } else {
        var date = new Date(timestamp * 1000);
    }
    //Some letters (F and M) need a space after them because of conflicts
    return string.replace(/Y/, date.getFullYear())//Year
                 .replace(/y/, date.getFullYear().toString().substr(2))//Year (two digits)
                 .replace(/m/, dateAddZero(date.getMonth() + 1))//Month (with zero)
                 .replace(/n/, (date.getMonth() + 1))//Month
                 .replace(/d/, dateAddZero(date.getDate()))//Day (with zero)
                 .replace(/j/, date.getDate())//Day
                 .replace(/w/, date.getDay())//Day of the week number
                 .replace(/W/, date.getWeek())//Week number
                 .replace(/z/, date.getYearsDay())//Day number in the year
                 .replace(/H/, dateAddZero(date.getHours()))//Hour (with zero)
                 .replace(/G/, date.getHours())//Hour
                 .replace(/i/, dateAddZero(date.getMinutes()))//Minutes (with zero)
                 .replace(/s/, dateAddZero(date.getSeconds()))//Seconds (with zero)
                 .replace(/S/, date.getSuffix())//Day's suffix (th, nd, st...)
                 .replace(/D/, date.getDaysName())//Day of the week name (long)
                 .replace(/l/, date.getDaysName(true))//Day of the week name (short)
                 .replace(/F([^r])?/, date.getMonthsName() + "$1")//Month name (long)
                 .replace(/M([^oa])?/, date.getMonthsName(true) + "$1");//Month name (short)
}

//This functions returns the UNIX timestamp
function time() {
    return Math.round((new Date()).getTime() / 1000);
}
