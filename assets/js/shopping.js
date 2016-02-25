function Item(lId) {
    this.values = {
        iList: lId
    };
    this.init = function() {
        this.values.iUser = 0;
        this.values.iContent = "";
        this.values.iCreationDate = date("T", new Date());
        this.values.iModificationDate = date("T", new Date());
        this.values.iStatus = 0;
    }
    
    this.loadFromData = function(data) {
        this.values.iId = data.iId;
        this.values.iUser = data.iUser;
        this.values.iContent = data.iContent;
        this.values.iCreationDate = data.iCreationDate;
        this.values.iModificationDate = data.iModificationDate;
        this.values.iStatus = data.iStatus;
    }
    
    this.render = function(editable) {
        if(editable) {
            this.LI = $('<li data-item-id="' + this.values.iId + '"><i class="fa fa-' + (this.values.iStatus == 1 ? "check-" : "") + 'square-o fa-fw checkbox"></i> <input type="text" value="' + this.values.iContent + '" class="item_content" /></li>');
        } else {
            this.LI = $('<li data-item-id="' + this.values.iId + '"><i class="fa fa-' + (this.values.iStatus == 1 ? "check-" : "") + 'square-o fa-fw checkbox"></i> ' + this.values.iContent + '</li>');
        }
        return this.LI;
    }
}

function List() {
    this.items = [];
    this.values = {};
    
    this.init = function() {
        this.values = {
            lTitle: "",
            lIcon: "list",
            lCreationDate: date("T", new Date()),
            lModificationDate: date("T", new Date())
        };
        var item = new Item();
        item.init();
        this.items.push(item);
        this.populateForm();
    };
    
    this.loadFromData = function(data) {
        this.values = data;
    }
    
    this.loadFromId = function(lId) {
        $.ajax({
            url: "ajax/shopping.php",
            method: 'post',
            data: {
                action: "get-list",
                lId: lId
            },
            context: this,
            success: function(data) {
                ajaxDebug(data);
                this.loadFromData(data.list);
                this.items = [];
                for(var i = 0 ; i < data.list.items.length ; i++) {
                    var item = new Item();
                    item.loadFromData(data.list.items[i]);
                    this.items.push(item);
                }
                this.populateForm();
                $("#loadingColumn").hide();
                $("#listColumn").show();
            }
        });
    };
    
    this.populateForm = function() {
        $("#list_items").empty();
        $("#list_title").val(this.values.lTitle);
        if(this.values.lGroup) {
            $("#list_group").val(this.values.lGroup);
        } else {
            $("#list_group").val($("#list_group option:first").val());
        }
        $("#list_icon").attr("class", "fa fa-" + this.values.lIcon + " fa-fw");
        $("#list_creation_date").text(date("d/m/Y Hhi", this.values.lCreationDate));
        $("#list_modification_date").text(date("d/m/Y Hhi", this.values.lModificationDate));
        for(var i = 0 ; i < this.items.length ; i++) {
            var item = this.items[i];
            $("#list_items").append(item.render(true));
        }
        $("#listColumn").show();
        $("#defaultColumn").hide();
        $("#list_save").prop("disabled", true);
    }
    
    this.addItem = function(position) {
        if(position === undefined) {
            var position = this.items.length - 1;
        }
        var item = new Item();
        item.init();
        this.items.splice(position, 0, item);
        var li = item.render(true);
        if(position === undefined || this.items.length == 1) {
            $("#list_items").append(li);
        } else {
            $("#list_items li:eq(" + position + ")").after(li);   
        }
        $(li).find("input").focus();
        this.modified();
    }
    
    this.removeItem = function(i) {
        var item = this.items.splice(i, 1);
        this.focusPreviousItem();
        $("#list_items li:eq(" + i + ")").remove();
        this.modified();
    }
    
    this.moveUp = function(i) {
        if(i <= 0) {
            return false;
        }
        var item = this.items.splice(i, 1);
        this.items.splice(i - 1, 0, item[0]);
        $("#list_items li:eq(" + i + ")").insertBefore($("#list_items li:eq(" + i + ")").prev()).find("input").focus();
        this.modified();
    }
    
    this.moveDown = function(i) {
        if(i >= this.items.length - 1) {
            return false;
        }
        var item = this.items.splice(i, 1);
        this.items.splice(i, 0, item[0]);
        $("#list_items li:eq(" + i + ")").insertAfter($("#list_items li:eq(" + i + ")").next()).find("input").focus();
        this.modified();
    }
    
    this.focusNextItem = function() {
        $("#list_items input:focus").parent().next().find("input").focus();
    }
    this.focusPreviousItem = function() {
        $("#list_items input:focus").parent().prev().find("input").focus();
    }
    
    this.modified = function() {
        $("#list_save").prop("disabled", false);
    }
    
    this.update = function() {
        this.values.lTitle = $("#list_title").val();
        this.values.lGroup = $("#list_group").val();
        for(var i = 0 ; i < this.items.length ; i++) {
            this.items[i].values.iOrder = i;
            this.items[i].values.iContent = $("#list_items li:eq(" + i + ") input").val();
            this.items[i].values.iStatus = $("#list_items li:eq(" + i + ") .checkbox").hasClass("fa-check-square-o") ? 1 : 0;
            this.items[i].values.iModificationDate = date("T", new Date());
        }
    }
    
    this.save = function() {
        this.update();
        if(this.values.lTitle == "") {
            warning("Donnez un titre à cette liste");
            return false;
        }
        var list = this.values;
        list.items = [];
        for(var i = 0 ; i < this.items.length ; i++) {
            var item = this.items[i].values;
            item.iOrder = i;
            list.items.push(item);
        }
        $("#list_save").prop("disabled", true).html('<i class="fa fa-spinner fa-pulse fa-fw"></i> En cours...');
        $.ajax({
            url: "ajax/shopping.php",
            method: 'post',
            data: {
                action: $("#menu li.active a").attr("href") == 0 ? "create-list" : "edit-list",
                list: list,
                lId: this.values.lId
            },
            context: this,
            success: function(data) {
                $("#list_save").prop("disabled", false).html('<i class="fa fa-save fa-fw"></i> Enregistrer');
                ajaxDebug(data);
                if(data.status) {
                    this.values.lId = data.list.lId;
                    this.values.lCreationDate = data.list.lCreationDate;
                    this.values.lModificationDate = data.list.lModificationDate;
                    for(var i = 0 ; i < data.list.items.length ; i++) {
                        this.items[i].values = data.list.items[i];
                    }
                    this.updateForm();
                    $("#list_save").prop("disabled", true);
                    $("#list_creation_date").text(date("d/m/Y Hhi", this.values.lCreationDate));
                    $("#list_modification_date").text(date("d/m/Y Hhi", this.values.lModificationDate));
                } else {
                    error(data.error);
                }
            },
            error: function() {
                $("#list_save").prop("disabled", false).html('<i class="fa fa-save fa-fw"></i> Enregistrer');
                error("Impossible d'enregistrer la liste");
            }
        });
    }
    
    this.updateForm = function(opt_init) {
        if(opt_init !== true) {
            //Update items
            for(var i = 0 ; i < this.items.length ; i++) {
                $("#list_items li:eq(" + this.items[i].values.iOrder + ")").data("item-id", this.items[i].values.iId);
            }
        }
        $("#menu li").removeClass('active');
        if($("#menu_lists [href=" + this.values.lId + "]").size()) {
            $("#menu_lists [href=" + this.values.lId + "]").parent().addClass("active");
            $("#menu_lists [href=" + this.values.lId + "] i").attr("class", "fa fa-" + this.values.lIcon + " fa-fw");
        } else {
            //Add menu item
            $("#menu_lists").append('<li' + (opt_init !== true ? ' class="active"' : '') + '><a href="' + this.values.lId + '"><i class="fa fa-' + this.values.lIcon + ' fa-fw"></i> <span>' + this.values.lTitle + '</span></a></li>');
        }
    }
    
    this.delete = function() {
        $.ajax({
            url: "ajax/shopping.php",
            method: 'post',
            data: {
                action: "delete-list",
                lId: this.values.lId
            },
            context: this,
            success: function(data) {
                ajaxDebug(data);
                if(data.status) {
                    $("#menu_lists [href=" + this.values.lId + "]").parent().remove();
                    page_init();
                } else {
                    error(data.error);
                }
            }
        });
    }
    
    this.clear = function() {
        $(".checkbox.fa-check-square-o").each(function() {
            var li = $(this).parent();
            currentList.removeItem($("#list_items").children().index(li));
        });
    }
}

function page_init() {
    $("#listColumn").hide();
    $("#loadingColumn").hide();
    $("#defaultColumn").show();
}

var currentList = null;

$(document).ready(function() {
    page_init();
    $.ajax({
        url: "ajax/shopping.php",
        method: 'post',
        data: {
            action: "page-init"
        },
        success: function(data) {
            ajaxDebug(data);
            if(data.status) {
                $("#menu_lists").empty();
                for(var i = 0 ; i < data.lists.length ; i++) {
                    var list = new List();
                    list.loadFromData(data.lists[i]);
                    list.updateForm(true);
                }
                $("#list_group").empty();
                for(var i = 0 ; i < data.groups.length ; i++) {
                    $("#list_group").append('<option value="' + data.groups[i].gId + '">' + data.groups[i].gName + '</option>');
                }
            } else {
                error(data.error);
            }
        }
    });
    
    /**
     * Triggers
     */
    $("#shoppingModeToggle").click(function() {
        $("body").toggleClass("shopping-mode");
        $("#list li").each(function() {
            if($("#shoppingModeToggle").hasClass('active')) {
                $(this).find(".item_content").each(function() {
                    $(this).replaceWith('<input class="item_content" type="text" value="' + $(this).text() + '" />');
                });
            } else {
                $(this).find(".item_content").each(function() {
                    $(this).replaceWith('<p class="item_content">' + $(this).val() + '</p>');
                });
            }
        });
    });
    
    $("#menu").on("click", "a", function(e) {
        e.preventDefault();
        if($(this).parent().hasClass("active")) {
            return false;
        } else {
            if(!$("#list_save").prop("disabled") && !confirm("Les modifications que vous n'avez pas enregistrées seront perdues. Continuer ?")) {
                return false;
            }
            $("#menu li").removeClass("active");
            $(this).parent().addClass("active");
        }
        currentList = new List();
        if($(this).attr("href") == 0) {
            currentList.init();
            $("#list_delete").addClass("disabled");
        } else {
            $("#defaultColumn").hide();
            $("#loadingColumn").show();
            $("#listColumn").hide();
            currentList.loadFromId($(this).attr("href"));
            $("#list_delete").removeClass("disabled");
        }
    });
    
    $("#list_items").on('click', '.checkbox', function() {
        $(this).toggleClass('fa-check-square-o fa-square-o');
        currentList.modified();
    });
    
    $("#list_items").on('click', 'p.item_content', function() {
        $(this).prev().toggleClass('fa-check-square-o fa-square-o');
        currentList.modified();
    });
    
    $("#list_items").on("input", "input.item_content", function(e) {
        currentList.modified();
    });
    
    $("#list_title").on("input", function(e) {
        currentList.modified();
    });
    
    $("#list_group").on("change", function(e) {
        currentList.modified();
    });
    
    $("#item_add").click(function(e) {
        e.preventDefault();
        currentList.addItem();
    });
    
    $("#list_save").click(function(e) {
        e.preventDefault();
        currentList.save();
    });
    
    $("#list_delete").click(function(e) {
        e.preventDefault();
        if(confirm("Voulez-vous vraiment supprimer cette liste")) {
            currentList.delete();
        }
    });
    
    $("#list_icon").click(function(e) {
        e.preventDefault();
        $("#modal-icons").modal("show");
    });
    
    $("#list_clear").click(function(e) {
        e.preventDefault();
        currentList.clear();
    });
    
    $("#modal-icons .btn").click(function() {
        currentList.values.lIcon = $(this).data("value");
        $("#list_icon").attr("class", "fa fa-" + currentList.values.lIcon + " fa-fw");
        currentList.modified();
        $("#modal-icons").modal("hide");
    });
    
    $("#list_items").on("keydown", "input.item_content", function(e) {
        if(e.ctrlKey) {
            switch(e.keyCode) {
                case 32://space
                    e.preventDefault();
                    $(this).prev().toggleClass('fa-check-square-o fa-square-o');
                    currentList.modified();
                    break;
                case 83://s
                    e.preventDefault();
                    currentList.save();
                    break;
                case 38://up
                    var li = $(this).parent();
                    currentList.moveUp($("#list_items").children().index(li));
                    break;
                case 40://down
                    var li = $(this).parent();
                    currentList.moveDown($("#list_items").children().index(li));
                    break;
            }
        } else {
            switch(e.keyCode) {
                case 8://delete
                    if($(this).val() == "") {
                        e.preventDefault();
                        var li = $(this).parent();
                        currentList.removeItem($("#list_items").children().index(li));
                    }
                    break;
                case 13://enter
                    var li = $(this).parent();
                    currentList.addItem($("#list_items").children().index(li));
                    break;
                case 38://up
                    currentList.focusPreviousItem();
                    break;
                case 40://down
                    currentList.focusNextItem();
                    break;
            }
        }
    });
});

window.onbeforeunload = function() {
    if(!$("#list_save").prop("disabled")) {
        return "Les modifications que vous n'avez pas enregistrées seront perdues. Continuer ?";
    }
};
