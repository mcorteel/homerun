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
                debug(data.list.items.length + " items");
                for(var i = 0 ; i < data.list.items.length ; i++) {
                    var item = new Item();
                    item.loadFromData(data.list.items[i]);
                    this.items.push(item);
                }
                this.populateForm();
            }
        });
    };
    
    this.populateForm = function() {
        $("#list_items").empty();
        $("#list_title").val(this.values.lTitle);
        $("#list_icon").attr("class", "fa fa-" + this.values.lIcon + " fa-fw");
        $("#list_creation_date").text(date("d/m/Y Hhi", this.values.lCreationDate));
        $("#list_modification_date").text(date("d/m/Y Hhi", this.values.lModificationDate));
        for(var i = 0 ; i < this.items.length ; i++) {
            var item = this.items[i];
            $("#list_items").append(item.render(true));
        }
        $("#listColumn").show();
        $("#defaultColumn").hide();
    }
    
    this.addItem = function(position) {
        if(position === undefined) {
            var position = this.items.length - 1;
        }
        info("Add item after " + position);
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
            error("Donnez un titre à cette liste");
            return false;
        }
        var list = this.values;
        list.items = [];
        for(var i = 0 ; i < this.items.length ; i++) {
            var item = this.items[i].values;
            item.iOrder = i;
            list.items.push(item);
        }
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
                ajaxDebug(data);
                if(data.status) {
                    this.values.lId = data.list.lId;
                    for(var i = 0 ; i < data.list.items.length ; i++) {
                        this.items[i].values = data.list.items[i];
                    }
                    this.updateForm();
                } else {
                    info(data.error);
                }
            }
        });
        $("#list_save").prop("disabled", true);
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
                    info(data.error);
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
                info(data.error);
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
            $("#menu li").removeClass("active");
            $(this).parent().addClass("active");
        }
        currentList = new List();
        if($(this).attr("href") == 0) {
            currentList.init();
        } else {
            currentList.loadFromId($(this).attr("href"));
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
        $("#modal-icons").modal("hide");
    });
    
    $("#list_items").on("input", "input.item_content", function(e) {
        currentList.modified();
    });
    
    $("#list_title").on("input", function(e) {
        currentList.modified();
    });
    
    $("#list_items").on("keydown", "input.item_content", function(e) {
        if(e.ctrlKey) {
            switch(e.keyCode) {
                case 32://space
                    
                    break;
                case 46://delete
                    currentList.removeItem($(this).parent().index($("#list_items")));
                    break;
            }
        } else {
            switch(e.keyCode) {
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
