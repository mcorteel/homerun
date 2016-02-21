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

$(document).ready(function(){
    //DATEPICKER
    $(".date-container").datepicker({weekStart: 1, format: "dd/mm/yyyy", todayHighlight: true, language:"fr"}).on("changeDate", function(e){
        $(".iDate").val(date("d/m/Y", Math.round(e.date / 1000)));
    });

    $(".iDate").focus(function(){
        $(".date-container").addClass("active");
    });
    $(".iDate").blur(function(){
        $(".date-container").removeClass("active");
    });
    $(".iDate").on("keydown", function(e){
        if(e.keyCode < 37 || e.keyCode > 40) {
            return;
        }
        var cD = Math.round($(".date-container").datepicker("getUTCDate").getTime() / 1000);
        var nD = cD;
        switch(e.keyCode) {
            case 37:    nD = cD - 3600 * 24;        break;
            case 38:    nD = cD - 3600 * 24 * 7;    break;
            case 39:    nD = cD + 3600 * 24;        break;
            case 40:    nD = cD + 3600 * 24 * 7;    break;
        }
        $(".date-container").datepicker("update", date("d-m-Y", nD));
    });

    $("#addInputFixed").click(showPersonalInputModal);
});

function showPersonalInputModal() {
    $("#modalInput .iId").val(0);
    $("#modalInput .iNotes").val("");
    $("#modalInput .iAmount").val("");
    $("#modalInput .date-container").datepicker("update", date("d-m-Y"));
    $("#modalInput").modal("toggle");
}

$("#modalInput").on("shown.bs.modal", function(){$("#modalInput .iNotes").focus()});

function sendPersonalInput() {
    $(".#modalInput .alert").remove();
    var data = new Object();
    data.iAmount = parseFloat($("#modalInput .iAmount").val());
    data.action = $("#modalInput .iId").val() == 0 ? "create" : "edit";
    data.iType = $("#modalInput .iType .btn.active input").val();
    data.iId = $("#modalInput .iId").val();
    data.iDate = date("Y-m-d", $("#modalInput .date-container").datepicker("getDate").getTime() / 1000);
    data.iNotes = $("#modalInput .iNotes").val();
    data.account = account;
    if(isNaN(data.iAmount)) {
        data.iAmount = 0;
    }
    if(data.iAmount <= 0) {
        $("#modalInput .form-horizontal").append("<p class=\"alert alert-warning\">Le montant doit être un nombre strictement positif.</p>");
        return false;
    }
    $("#modalInput .wait").show();
    $("#modalInput .form-horizontal").hide();
    $.post("ajax/accounts.php", data, function(data){
        ajaxDebug(data);
        $("#modalInput").hide();
        $(".table").show();
        info(data.message);
    });
    return false;
}

function toggleDatepicker(display) {
    if(display === undefined) {
        $(".date-container").toggleClass("display");
        $(".toggle-datepicker").toggleClass("active");
    } else if(display) {
        $(".date-container").addClass("display");
        $(".toggle-datepicker").addClass("active");
    } else {
        $(".date-container").removeClass("display");
        $(".toggle-datepicker").removeClass("active");
    }
}
