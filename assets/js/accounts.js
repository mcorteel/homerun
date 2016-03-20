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
    n = parseInt(location.hash.replace("#page", ""));
    if(isNaN(n)) {
        n = 1;
    }
    displayPage(n);
    
    $(".add").click(function(){
        if(searchActive) {
            $(".search").hide();
        }
        $(".add-input").show();
        clearForm();
        $(".add-input .form-horizontal").show();
        $(".add-input .wait").hide();
        $(".add-input h3").text("Ajouter une entrée");
        $(".add-input .form-actions .btn-primary").html("<i class=\"fa fa-plus\"></i> Ajouter");
        $(".add-input .iId").val(0);
        $(".add-input .iNotes").focus();
        $(".add-input").show();
        $(".table").hide();
        toggleDatepicker(false);
    });
    
    $(".add-input .form-actions .btn-default").click(function(){
        $(".add-input").hide();
        $(".table").show();
        if(searchActive) {
            $(".search").show();
        }
    });
    
    $(".delete").click(function(){
        if(confirm("Veuillez confirmer la suppression de « " + $("tr.active .iNotes").text() + " »")) {
            deleteInput($("tr.active").attr("data-id"));
        }
    });
    
    $(".add-input input:not(.iDate), .add-input select").keydown(function(e){
        if(e.keyCode == 27) {//ESC
            $(".add-input").hide();
            $(".table").show();
        }
    });
    
    $("body").keydown(function(e){
        if(!$(".add-input").is(":visible") && !searchActive) {
            switch(e.keyCode) {
                case 38://up
                    e.preventDefault();
                    if($("tr.active:not(:first-child)").size()) {
                        selectLine($("tr.active").prev().attr("data-id"));
                    }
                    break;
                case 40://down
                    e.preventDefault();
                    if(!$("tr.active").size()) {
                        selectLine($("tbody tr:first-child").attr("data-id"));
                    } else if($("tr.active").next().size()) {
                        selectLine($("tr.active").next().attr("data-id"));
                    }
                    break;
                case 80://p
                    displayPage("previous");
                    break;
                case 82://r
                    displayPage("refresh");
                    break;
                case 78://n
                    displayPage("next");
                    break;
                case 71://g
                    displayPage(prompt("Numéro de page"));
                    break;
                case 13://enter
                    e.preventDefault();
                    if($("tr.active").size()) {
                        editInput($("tr.active").attr("data-id"));
                    }
                    break;
                case 46://delete
                    e.preventDefault();
                    if($("tr.active").size() && confirm("Veuillez confirmer la suppression de « " + $("tr.active .iNotes").text() + " »")) {
                        deleteInput($("tr.active").attr("data-id"));
                    }
                    break;
                case 65://a
                    e.preventDefault();
                    $(".add").click();
                    break;
                case 70://f
                    e.preventDefault();
                    $(".toggle-search").click();
                    break;
            }
            if($("tr.active").size()) {
                if($("tr.active").offset().top - 126 < $(window).scrollTop()) {
                    $(window).scrollTop($("tr.active").offset().top - 126);
                }
                if($("tr.active").offset().top + $("tr.active").height() > $(window).scrollTop() + $(window).height()) {
                    $(window).scrollTop($(window).scrollTop() + 50);
                }
            }
        }
        if(searchActive && e.keyCode == 27) {//ESC
            endSearch();
        }
    });
    
    $(".previous-page").click(function(){
        displayPage("previous");
    });
    $(".next-page").click(function(){
        displayPage("next");
    });
    $(".gotoPage").click(function(){
        displayPage(prompt("Aller à la page :"));
    });
    
    /**
     * SEARCH
     */
    
    $(".toggle-search").click(function(){
        searchActive = !searchActive;
        if(searchActive) {
            $(".toggle-search").addClass("active");
            $(".search").show();
            $(".search .result").html("<i class=\"fa fa-hand-o-left fa-fw\"></i> Entrez une recherche</span>");
            $(".search input").focus().select();
            $(".table").addClass("down");
            $(".next-page, .previous-page").prop("disabled", true);
        } else {
            endSearch();
        }
    });
    
    $(".search .btn-primary").click(function(e){
        e.preventDefault();
        search($(".search input[type=text]").val());
    });
    
    /**
     * HASH CHANGE
     */
    
    $(window).on('hashchange', function() {
        n = parseInt(location.hash.replace("#page", ""));
        if(isNaN(n)) {
            n = 1;
        }
        displayPage(n);
    });
    
    if($(window).width() > 500) {
        $(".iAmount").replaceWith('<input class="form-control iAmount" type="text">');
    }
});

function updateView() {
    $(".table tbody tr").each(function() {
        if($(this).prev().find(".iDate").text() == $(this).find(".iDate").text()) {
            $(this).find(".iDate").empty();
        }
    });
}

function endSearch() {
    $(".search").hide();
    $(".search .result").empty();
    $(".table").removeClass("down");
    $(".toggle-search").removeClass("active");
    $(".next-page, .previous-page").prop("disabled", false);
    searchActive = false;
    n = parseInt(location.hash.replace("#page", ""));
    if(isNaN(n)) {
        n = 1;
    }
    displayPage(n, true);
}

function clearForm() {
    $(".add-input .iAmount").val("");
    $(".add-input .iNotes").val("");
    $(".add-input .date-container").datepicker("update", date("d-m-Y"));
    $(".add-input .wait").hide();
    $(".add-input .form-actions .btn").prop("disabled", false);
    $(".add-input .alert").remove();
    toggleDatepicker(false);
}

function getLine(t) {
    var d = new Date(t.iDate);
    var c = date("l", d).toLowerCase();
    return "<tr data-id=\"" + t.iId + "\" class=\"day-" + c + "\"><td>" + (t.iAmountValue < 0 ? "<span class=\"fa-overlay\"><i class=\"fa fa-fw fa-" + t.iIcon + "\"></i><i class=\"fa fa-fw fa-arrow-left\"></i></span>" : "<i class=\"fa fa-fw fa-" + t.iIcon + "\"></i>") + "</td><td class=\"iDate\">" + t.iDisplayDate + "</td><td><a href=\"javascript:editInput(" + t.iId + ");\" class=\"iNotes\">" + (t.iNotes == "" ? "<em>Sans description</em>" : t.iNotes) + "</a></td><td class=\"iAmount\">" + t.iAmount + "</td>" + (t.iUser ? "<td class=\"iUser\">" + t.iUser + "</td>" : "") + "</tr>";
}

function selectLine(id) {
    $("tr.active").removeClass("active");
    $("tr[data-id=" + id + "]").addClass("active");
    $(".delete").show();
}

function displayPage(n, force) {
    debug("Displaying page " + n);
    if(n == null) {
        return false;
    }
    if(n == "next") {
        n = pageNumber + 1
    }
    if(n == "previous") {
        n = pageNumber - 1;
    }
    if(n == "refresh") {
        n = pageNumber;
        force = true;
    }
    n = Math.max(Math.min(n, pagesCount), 1);
    if(n == pageNumber && force !== true) {
        debug("Same page, skipping");
        return false;
    }
    
    $(".pageNumber").html("<i class=\"fa fa-spinner fa-pulse fa-fw\"></i>");
    
    $.post("ajax/accounts.php", {action: "get-list", account: account, pageNumber: n}, function(data){
        if(data.status) {
            $(".table tbody").empty();
            for(var i in data.list) {
                $(".table tbody").append(getLine(data.list[i]));
            }
            updateView();
            pageNumber = n;
            location.hash = "#page" + n;
            pagesCount = data.pagesCount;
            if(pagesCount == 0) {
                $(".pageNumber").text("");
                $(".table tbody").html("<tr><td colspan=5 class=\"message\">Rien à afficher...</td></tr>");
                $(".next-page, .previous-page").prop("disabled", true);
            } else {
                $(".pageNumber").text(n + "/" + data.pagesCount);
                $("tr[data-id]").click(function(){
                    selectLine($(this).attr("data-id"));
                });
                $(".previous-page").prop("disabled", n == 1);
                $(".next-page").prop("disabled", n == pagesCount);
            }
            //Page initialization
            $(".delete").hide();
        } else {
            error(data.error);
            $(".pageNumber").text("?");
            $(".main-container").html("<p style=\"text-align:center;\"><a class=\"btn btn-primary\" href=\"money.html\">Aller à la liste des comptes</a></p>");
        }
    });
}

function deleteInput(id) {
    $(".table").hide();
    $(".add-input .form-horizontal").hide();
    $(".add-input .wait").show();
    $(".add-input").show();
    $.post("ajax/accounts.php", {action: "delete", account: account, iId: id}, function(data){
        $(".add-input").hide();
        $(".table").show();
        info(data.message);
        $("tr[data-id=" + data.iId + "]").remove();
        $(".delete").hide();
    });
}

function sendInput() {
    $(".add-input .alert").remove();
    var data = new Object();
    data.iAmount = parseFloat($(".add-input .iAmount").val().replace(",", "."));
    data.action = $(".add-input .iId").val() == 0 ? "create" : "edit";
    data.iType = $(".add-input .iType .btn.active input").val();
    data.iId = $(".add-input .iId").val();
    data.iDate = date("Y-m-d", $(".add-input .date-container").datepicker("getDate").getTime() / 1000);
    data.iNotes = $(".add-input .iNotes").val();
    data.iUser = $(".add-input .iUser").val();
    data.account = account;
    if(isNaN(data.iAmount)) {
        data.iAmount = 0;
    }
    $(".add-input .wait").show();
    $(".add-input .form-horizontal").hide();
    $.post("ajax/accounts.php", data, function(data){
        ajaxDebug(data);
        $(".add-input").hide();
        $(".table").show();
        info(data.message);
        if(data.action == "create") {
            $(".table td.message").remove();
            var s = getLine(data.input);
            if(data.prevId) {
                $("tr[data-id=" + data.prevId + "]").before(s);
            } else {
                $(".table tbody").prepend(s);
            }
            $("tr[data-id=" + data.input.iId + "]").click(function(){
                selectLine($(this).attr("data-id"));
            });
            if(searchActive) {
                $(".search").show();
            }
        } else {
            $("tr[data-id=" + data.input.iId + "] .fa").attr("class", "fa fa-fw fa-" + data.input.iIcon);
            $("tr[data-id=" + data.input.iId + "] .iNotes").html(data.input.iNotes ? data.input.iNotes : "<em>Sans description</em>");
            $("tr[data-id=" + data.input.iId + "] .iAmount").html(data.input.iAmount);
            $("tr[data-id=" + data.input.iId + "] .iDate").html(data.input.iDisplayDate);
            $("tr[data-id=" + data.input.iId + "] .iUser").html(data.input.iUser);
        }
        if(!searchActive) {
            pagesCount = data.pagesCount;
            displayPage(data.page);
        } else {
            $(".search").show();
            $(".table").addClass("down");
        }
    });
    return false;
}

function editInput(id) {
    $(".table").hide();
    $(".add-input .form-horizontal").hide();
    $(".add-input h3").text("Modifier une entrée");
    $(".add-input .wait").show();
    $(".add-input").show();
    toggleDatepicker(false);
    clearForm();
    $.post("ajax/accounts.php", {action: "get", account: account, iId: id}, function(data){
        $(".add-input .iId").val(data.input.iId);
        $(".add-input .date-container").datepicker("update", data.input.iDate.substr(8, 2) + "-" + data.input.iDate.substr(5, 2) + "-" + data.input.iDate.substr(0, 4));
        $(".add-input .iNotes").val(data.input.iNotes);
        $(".add-input .iAmount").val(data.input.iAmount);
        $(".add-input .iUser").val(data.input.iUserId);
        $(".add-input .iType .btn").removeClass("active");
        $(".add-input .iType .btn").filter(function(){return $(this).find("input").val() == data.input.iType;}).addClass("active");
        $(".add-input .wait").hide();
        $(".add-input .form-horizontal").show();
        $(".add-input .iNotes").focus();
        $(".add-input .form-actions .btn-primary").html("<i class=\"fa fa-edit\"></i> Modifier");
    });
    $(".search").hide();
    $(".table").removeClass("down");
}

/**
 * Search
 */

var searchActive = false;

function search(string) {
  searchActive = true;
    if(string === undefined) {
        var string = $(".search input").val();
    }
    debug("Displaying search page for " + string);
    $(".result").html("<i class=\"fa fa-spinner fa-pulse\"></i> Recherche en cours...");
    $.post("ajax/accounts.php", {action: "search-list", account: account, search: string}, function(data){
        ajaxDebug(data);
        if(data.status) {
            $(".table tbody").empty();        
            for(var i in data.list) {
                $(".table tbody").append(getLine(data.list[i]));
            }
            if(!data.list.length) {
                $(".table tbody").html("<tr><td colspan=5 class=\"message\">Rien à afficher...</td></tr>");
            }
            $(".previous-page, .next-page").prop("disabled", true);
            //Page initialization
            $(".delete").hide();
            $(".result").html(frenchPlural("%n résultat%s, total : " + data.total, data.hits));
            $("tr[data-id]").click(function(){
                selectLine($(this).attr("data-id"));
            });
        } else {
            error(data.error);
            $(".main-container").html("<p style=\"text-align:center;\"><a class=\"btn btn-primary\" href=\"money.html\">Aller à la liste des comptes</a></p>");
        }
    });
    return false;
}
