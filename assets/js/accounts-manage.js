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

function addTag(icon, name, opt_id, opt_fixed)
{
    var id = opt_id || 0;
    var fixed = opt_fixed || false;
    
    $(".tags li:not(.tag)").before('<li class="tag input-group" data-id="' + id + '"><span class="input-group-btn"><button class="btn btn-default tIcon" data-value="' + icon + '"><i class="fa fa-' + icon + ' fa-fw"></i></button></span><input type="text" class="form-control tName" value="' + name + '" placeholder="Nom du tag" />' + (fixed ? '' : '<span class="input-group-btn"><button class="btn btn-danger" title="Supprimer ce tag"><i class="fa fa-trash-o fa-fw"></i></button></span>') + '</li>');
    $(".tags li.tag:last .tIcon").click(function(){
        $(this).addClass("active");
        $("#modal-icons").modal("show");
    });
    $(".tags li.tag:last .btn-danger").click(function(){
        $(this).closest("li").remove();
    });
}

function init()
{
    $(".selection").show();
    $(".edition, .wait").hide();
    $(".menu li").removeClass("active");
    $(".menu a").unbind("click").click(function(e){
        e.preventDefault();
        $(this).blur();
        if($(this).parent().hasClass("active")) {
            return false;
        }
        $(".wait").show();
        $(".edition, .selection").hide();
        $(".menu li").removeClass("active");
        $(this).parent().addClass("active");
        var id = $(this).attr("href");
        if(id > 0) {
            $(".edition h3").text("Modifier « " + $(this).text() + " »");
            $(".actions").show();
            $("#aLimit").hide();
            $(".tags li.tag").remove();
            $.post("ajax/accounts.php", {action: "get-account", account: id}, function(data){
                ajaxDebug(data);
                $("#aLimit").toggle(data.account.aLog == 1);
                $(".aLimit").val(data.account.aLimit);
                $(".aName").val(data.account.aName);
                $(".aGroup").val(data.account.aGroup).prop("disabled", true);
                for(var i in data.tags) {
                    addTag(data.tags[i].tIcon, data.tags[i].tName, data.tags[i].tId, true);
                }
                $(".selection, .wait").hide();
                $(".edition").show();
                $(".btn-primary").text("Modifier").unbind("click").click(function(){
                    var data = {
                        aName: $(".aName").val(),
                        aLimit: $(".aLimit").val(),
                        account: $(".menu li.active a").attr("href"),
                        tags: [],
                        action: "edit-account"
                    }
                    $(".tags li.tag").each(function(){
                        data.tags.push({
                            tId: $(this).attr("data-id"),
                            tName: $(this).find(".tName").val(),
                            tIcon: $(this).find(".tIcon").attr("data-value")
                        });
                    });
                    $.post("ajax/accounts.php", data, function(data){
                        ajaxDebug(data);
                        if(data.status) {
                            info("Le compte a bien été modifié");
                            $(".menu li.active a").text($(".aName").val());
                            init();
                        } else {
                            error("Erreur");
                        }
                    });
                });
            });
        } else {
            $(".edition h3").text(id == 0 ? "Créer un compte" : "Créer un journal");
            $(".actions").hide();
            $(".aName").val("");
            $(".aLimit").val(0);
            $(".aGroup").prop("disabled", false);
            $(".tags li.tag").remove();
            $("#aLimit").toggle(id == -1);
            $(".aLog").prop("checked", id == -1);
            $(".btn-primary").text("Créer").unbind("click").click(function(){
                var data = {
                    aName: $(".aName").val(),
                    aGroup: $(".aGroup").val(),
                    aLog: $(".aLog").prop("checked") ? 1 : 0,
                    aLimit: $(".aLimit").val(),
                    tags: Array(),
                    action: "create-account"
                }
                $(".tags li.tag").each(function(){
                    data.tags.push({tName: $(this).find(".tName").val(), tIcon: $(this).find(".tIcon").attr("data-value")});
                });
                $.post("ajax/accounts.php", data, function(data){
                    ajaxDebug(data);
                    info("after request");
                    if(data.status) {
                        info("Le compte a bien été créé");
                        $(".menu h4:last").before("<li><a href=\"" + data.account.aId + "\">" + data.account.aName + "</a></li>");
                        init();
                    } else {
                        error("Erreur");
                    }
                });
            });
            $(".selection, .wait").hide();
            $(".edition").show();
        }
    });
}
    
$(document).ready(function(){
    init();
    $(".cancel").click(init);
    $("#modal-icons .btn").click(function(){
        $(".tags .btn.active").attr("data-value", $(this).attr("data-value")).find("i").attr("class", "fa fa-" + $(this).attr("data-value") + " fa-fw");
        $("#modal-icons").modal("hide");
    });
    $("#modal-icons").on("hide.bs.modal", function(){
        $(".tags .btn.active").removeClass("active");
    });
    $(".add-tag").click(function(){
        addTag("money", "", 0);
    });
    $(".delete").click(function(e){
        e.preventDefault();
        if(confirm("Voulez-vous vraiment supprimer ce compte ?")) {
            $.post("ajax/accounts.php", {action: "delete-account", account: $(".menu li.active a").attr("href")}, function(data){
                ajaxDebug(data);
                if(data.status) {
                    $(".menu li.active").remove();
                    info("Le compte a bien été supprimé");
                    init();
                } else {
                    error("Erreur");
                }
            });
        }
    });
    
    $(".aLog").change(function(){
        $("#aLimit").toggle($(".aLog").prop("checked"));
    });
});
