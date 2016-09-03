<?php
if(!isset($_GET['subPage'])) {
    $_GET['subPage'] = "list";
}
switch($_GET['subPage']) {
/**
 * VIEW: Table of entries + add entry
 **/
case "view":
    if(!isset($_GET['id'])) {
        $database = new Database();
        $groups = Array();
        foreach(User::getAuth()->getGroups() as $group) {
            array_push($groups, $group->getId());
        }
        $request = $database->prepare("SELECT aId FROM " . ENV_TABLES_PREFIX . "accounts WHERE aGroup IN (" . arrayToString($groups, "") . ") ORDER BY aName ASC");
        $request->execute();
        if($request->rowCount() == 1) {
            $line = $request->fetch();
            header("Location: /accounts/view/{$line['aId']}");
        } else {
            header("Location: /accounts/list.html");
        }
    } else {
        $account = new Account();
        $account->loadFromId($_GET['id']);
        ?>
        <div class="header-menu fixed">
            <button class="btn btn-primary add" title="Ajouter une entrée"><i class="fa fa-plus"></i></button>
            <button class="btn btn-danger delete" style="display:none;"><i class="fa fa-trash-o"></i></button>
            <span class="right-menu pull-right">
                <span class="pageNumber gotoPage"></span>
                <span class="btn-group">
                    <button class="btn btn-default previous-page" title="Page précédente"><i class="fa fa-chevron-left"></i></button>
                    <button class="btn btn-default toggle-search" title="Search"><i class="fa fa-search"></i></button>
                    <button class="btn btn-default next-page" title="Page suivante"><i class="fa fa-chevron-right"></i></button>
                </span>
                <button class="btn btn-default menu"><i class="fa fa-reorder"></i></button>
            </span>
            <div class="search ">
                <div class="col-md-6">
                    <form class="input-group" onsubmit="return search();">
                        <input type="text" class="form-control" />
                        <span class="input-group-btn"><button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button></span>
                    </form>
                </div>
                <div class="col-md-6 result">
                
                </div>
            </div>
        </div>

        <table class="table table-condensed table-hover">
            <tbody>
                <td class="message"><i class="fa fa-spinner fa-pulse"></i> Un instant...</td>
            </tbody>
        </table>
        
        <form class="add-input col-md-offset-2 col-md-8" onsubmit="return sendInput();" novalidate>
            <h3 class="title">Ajouter une entrée</h3>
            <div class="form-horizontal">
                <input type="hidden" class="iId" value="0" />
                <div class="form-group">
                    <label class="control-label col-md-4">Type</label>
                    <div class="controls col-md-8 iType">
                        <div class="row" style="margin: 0 -5px;">
                            <?php
                            foreach($account->getTags() as $tag)
                            {
                                ?>
                                <div class="col-xs-4" data-toggle="buttons" style="padding: 0 5px; margin-bottom: 5px;">
                                    <label class="btn btn-block btn-default<?php if(!$i++) echo " active"; ?>">
                                        <input type="radio" name="iType" value="<?php echo $tag->getId(); ?>"><?php echo $tag->tName; ?>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">Date</label>
                    <div class="controls col-md-8">
                        <div class="input-group date-control">
                            <input type="text" value="<?php echo date("d/m/Y"); ?>" class="form-control iDate" />
                            <span class="input-group-addon btn btn-default toggle-datepicker" onclick="toggleDatepicker();"><i class="fa fa-calendar"></i></span>
                        </div>
                        <div class="date-container">
                        
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">Motif</label>
                    <div class="controls col-md-8">
                        <input type="text" class="form-control iNotes" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">Montant</label>
                    <div class="controls col-md-8">
                        <span class="input-group">
                            <input type="number" step="0.01" class="form-control iAmount"/>
                            <span class="input-group-addon">€</span>
                        </span>
                    </div>
                </div>
                <?php
                if(!$account->isLog()) {
                ?>
                <div class="form-group">
                    <label class="control-label col-md-4">Payé par</label>
                    <div class="controls col-md-8">
                        <?php
                        $users = Array();
                        foreach($account->getGroup()->getMembers() as $user) {
                            $users[$user->getId()] = $user->uDisplayName;
                        }
                        echo UI::select("iUser", $users, User::getAuth()->getId(), "iUser", true);
                        ?>
                    </div>
                </div>
                <?php
                }
                ?>
                <div class="form-actions">
                    <div class="col-md-offset-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Ajouter</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </div>
            <div class="wait">
                <i class="fa fa-spinner fa-pulse"></i> Un instant...
            </div>
        </form>
        <script type="text/javascript">
        var pageNumber = 0;
        var pagesCount = 0;
        var account = <?php echo $_GET['id']; ?>;
        </script>
        <script type="text/javascript" src="assets/js/accounts.js"></script>
        <?php
    }
    break;
/**
 * MANAGE: Create or edit accounts
 **/
case "manage":
    ?>
    <div class="header-menu fixed">
        <span class="title-pull">Gérer les comptes</span>
        <button class="btn btn-default pull-left" id="drawer_toggle"><i class="fa fa-caret-right"></i></button>
        <button class="btn btn-default menu pull-right"><i class="fa fa-reorder"></i></button>
    </div>
    <div class="container padded-container content">
        <div class="row">
            <div class="col-md-3" id="drawer">
                <ul class="nav nav-pills nav-stacked menu">
                    <h4>Comptes/journaux existants</h4>
                    <?php
                    $accounts = User::getAuth()->getAccounts(true);
                    foreach($accounts as $account) {
                        if(User::getAuth()->isAdminOf($account->getGroup()->gName)) {
                            echo "<li><a href=\"{$account->getId()}\"><i class=\"fa fa-{$account->aIcon} fa-fw\"></i> {$account->aName}</a></li>";
                        }
                    }
                    if(!sizeof($account)) {
                        echo "<li><em>Aucun compte</em></li>";
                    }
                    ?>
                    <h4>Actions</h4>
                    <li><a href="0"><i class="fa fa-plus fa-fw"></i> Créer un compte</a></li>
                    <li><a href="-1"><i class="fa fa-plus fa-fw"></i> Créer un journal</a></li>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="selection">
                    <h4>&nbsp;</h4>
                    <?php echo UI::info("Choisissez une action dans le menu."); ?>
                </div>
                <div class="wait">
                    <h4>&nbsp;</h4>
                    <p class="message"><i class="fa fa-spinner fa-pulse"></i> Un instant...</p>
                </div>
                <div class="edition form-horizontal">
                    <h4>Informations générales</h4>
                    <div class="form-group">
                        <label class="control-label col-md-2">Nom</label>
                        <div class="controls col-md-10">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <button class="btn btn-default btn-icon aIcon" type="button" data-value="money"><i class="fa fa-money fa-fw"></i></button>
                                </div>
                                <input type="text" class="form-control aName" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2">Groupe</label>
                        <div class="controls col-md-10">
                            <?php
                            $groups = Array();
                            foreach(User::getAuth()->getGroups() as $group) {
                                if(User::getAuth()->isAdminOf($group->gName)) {
                                    $groups[$group->getId()] = $group->gName;
                                }
                            }
                            echo UI::select("aGroup", $groups, "", "aGroup", true);
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2">Tags</label>
                        <div class="controls col-md-10">
                            <ul class="tags">
                                <li><button class="btn btn-success add-tag"><i class="fa fa-plus fa-fw"></i></button></li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group hidden">
                        <div class="controls col-md-10 col-md-offset-2">
                            <div class="checkbox">
                                <label><input type="checkbox" class="aLog"> Créer un journal</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="aLimit">
                        <label class="control-label col-md-2">Limite</label>
                        <div class="controls col-md-10">
                            <input type="text" class="form-control aLimit" />
                        </div>
                    </div>
                    <div class="col-md-10 col-md-offset-2 form-actions">
                        <button class="btn btn-primary">Créer</button>
                        <button class="btn btn-default cancel">Annuler</button>
                    </div>
                    <div class="actions">
                        <h4>Actions</h4>
                        <ul class="fa-ul">
                            <li><a href="#" class="delete"><i class="fa fa-times fa-li"></i>Supprimer ce compte</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    echo UI::iconsModal();
    ?>
    <script type="text/javascript" src="assets/js/accounts-manage.js"></script>
    <?php
    break;
/**
 * TRANSFERS: List of transfers + add transfer
 **/
case "transfers":
    ?>
    <div class="header-menu fixed">
        <span class="title-pull">Transferts</span>
        <span class="pull-right">
        <?php
        $accounts = Array();
        foreach(User::getAuth()->getAccounts() as $account) {
            $accounts[$account->getId()] = $account->aName;
        }
        echo UI::select("account", $accounts, $_GET['id'], "account", true);
        ?>
        </span>
    </div>
    <table class="table table-condensed transfers">
        <thead>
            <tr><th>Date</th><th>De</th><th>À</th><th>Somme</th><th>Motif</th><th></th></tr>
            <tr>
                <td class="date"><input type="text" value="<?php echo date("d/m/Y"); ?>" class="form-control tDate" /></td>
                <td><select class="form-control tSender"></select></td>
                <td><select class="form-control tReceiver"></select></td>
                <td><input type="number" class="form-control tAmount" autofocus /></td>
                <td><input type="text" class="form-control tNotes" /></td>
                <td class="actions"><button class="btn btn-success create-transfer"><i class="fa fa-plus fa-fw"></i></button></td>
            </tr>
        </thead>
        <tbody>
        
        </tbody>
    </table>
    <script type="text/javascript">
    function getLine(transfer) {
        return $("<tr data-id=\"" + transfer.tId + "\"><td>" + transfer.tDate + "</td><td>" + transfer.sender + "</td><td>" + transfer.receiver + "</td><td>" + transfer.tAmount + "</td><td>" + (transfer.tNotes == "" ? "<em>Sans titre</em>" : transfer.tNotes) + "</td><td class=\"actions\"><button class=\"btn btn-danger btn-xs\"><i class=\"fa fa-trash-o fa-fw\"></i></button></td></tr>").find(".btn-danger").click(function(){
            if(confirm("Voulez-vous vraiment supprimer ce transfert ?")) {
                $.post("ajax/accounts.php", {action: "delete-transfer", account: $(".account").val(), tId: $(this).closest("tr").attr("data-id")}, function(data){
                    ajaxDebug(data);
                    if(data.status) {
                        $("tr[data-id=" + data.tId + "]").remove();
                        info(data.message);
                    } else {
                        error(data.error);
                    }
                });
            }
        }).closest("tr");
    }
    
    function displayTransfers() {
        $.post("ajax/accounts.php", {action: "get-transfers", account: $(".account").val()}, function(data){
            ajaxDebug(data);
            $("table tbody").empty();
            for(var i in data.transfers) {
                $("table tbody").append(getLine(data.transfers[i]));
            }
            $(".tSender").empty();
            $(".tReceiver").empty();
            $(".tAmount").val("");
            $(".tNotes").val("");
            for(var i in data.users) {
                $(".tSender").append("<option value=\"" + i + "\">" + data.users[i] + "</option>");
                $(".tReceiver").append("<option value=\"" + i + "\">" + data.users[i] + "</option>");
            }
            $(".tSender").val(data.defaultSender);
        });
    }
    
    $(document).ready(function(){
        displayTransfers();
        $(".account").change(displayTransfers);
        $(".create-transfer").click(function(){
            if($(".tAmount").val() != "") {
                $.post("ajax/accounts.php", {action: "create-transfer", tAmount: $(".tAmount").val(), tNotes: $(".tNotes").val(), tSender: $(".tSender").val(), tReceiver: $(".tReceiver").val(), tDate: date("Y-m-d", Math.round($(".tDate").datepicker("getDate").getTime() / 1000)), account: $(".account").val()}, function(data){
                    ajaxDebug(data);
                    if(data.status) {
                        $("table tbody").prepend(getLine(data.transfer));
                        $(".tDate").datepicker("setDate", new Date());
                        $(".tAmount").val("");
                        $(".tNotes").val("");
                        info(data.message);
                    } else {
                        error(data.error);
                    }
                });
            } else {
                error("Le montant doit être un nombre positif");
            }
        });
        $(".tDate").datepicker({format: "dd/mm/yyyy", weekStart: 1});
    });
    </script>
    <?php
    break;
case "solution":
    $account = new Account();
    $account->loadFromId($_GET['id']);
    ?>
    <div class="header-menu fixed">
        <h3>Solution pour le compte « <?php echo $account->aName; ?> »</h3>
    </div>
    <div class="content">
    <?php
    $database = new Database();
    $users = $account->getGroup()->getMembers();
    foreach($users as $user) {
        echo "<h3 class=\"title\">{$user->uDisplayName}</h3>";
    }
    ?>
    </div>
    <?php
    break;
default:
    echo UI::error("Cette action n'est pas valable.");
    break;
}
?>
