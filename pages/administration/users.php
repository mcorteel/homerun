<?php
if(isset($_GET['id'])) {
    $database = new Database();
    $request = $database->prepare("SELECT gName FROM " . ENV_TABLES_PREFIX . "groups ORDER BY gName ASC");
    $request->execute();
    $groupsArray = Array();
    while($line = $request->fetch()) {
        $groupsArray[] = $line['gName'];
    }
    if($_GET['id'] == 0) {
        echo "<form class=\"form-horizontal\" method=\"post\">";
        $u = new User();
        if(isset($_POST['create-user'])) {
            if($_POST['user-uLogin'] != "" && $_POST['user-uDisplayName'] != "" && $_POST['user-uEmail'] != "" && $_POST['user-uPassword'] != "") {
                $oldStatus = $u->uStatus;
                $u->updateFromForm("post", "user-");
                $u->uStatus = 1;
                $u->uPassword = crypt($_POST['user-uPassword']);
                $u->setOption("reset_password", true);
                $u->create();
                $groups = stringToArray($_POST['user-groups']);
                $u->setGroups($groups);
                echo UI::info("L'utilisateur {$u->toString()} a bien été créé.");
            } else {
                echo UI::error("Remplissez tous les champs");
            }
        }
        ?>
        <fieldset>
            <legend>Créer un nouvel utilisateur</legend>
            <div class="form-group">
                <label class="control-label col-md-2">Identifiant</label>
                <div class="controls col-md-4">
                    <input class="form-control" type="text" name="user-uLogin" value="<?php echo $_POST['user-uLogin']; ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-2">E-mail</label>
                <div class="controls col-md-4">
                    <input class="form-control" type="text" name="user-uEmail" value="<?php echo $_POST['user-uEmail']; ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-2">Mot de passe</label>
                <div class="controls col-md-4">
                    <input class="form-control" type="password" name="user-uPassword" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-2">Nom d'affichage</label>
                <div class="controls col-md-4">
                    <input class="form-control" type="text" name="user-uDisplayName" value="<?php echo $_POST['user-uDisplayName']; ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-2">Groupes</label>
                <div class="col-md-2">
                    <ul id="user-groups-list">
                    </ul>
                </div>
                <div class="col-md-2">
                    <input type="text" id="groups-list" class="form-control" autocomplete="off" spellcheck="false" placeholder="Ajouter un groupe" />
                </div>
                <input type="hidden" autocomplete="off" name="user-groups" id="user-groups"  value="<?php echo isset($_POST['user-groups']) ? $_POST['user-groups'] : ""; ?>" />
            </div>
        </fieldset>
        <div class="form-actions">
            <div class="col-md-offset-2">
                <button class="btn btn-primary" name="create-user" type="submit"><i class="fa fa-user-plus"></i> Créer l'utilisateur</button>
                <a class="btn btn-default" href="administration/users.html">Annuler</a>
            </div>
        </div>
    <?php
    } else {
        echo "<div class=\"row\">";
        echo "<div class=\"col-md-6\">";
        echo "<form class=\"form-horizontal\" method=\"post\" autocomplete=\"off\">";
        $u = new User();
        $u->loadFromId($_GET['id']);
        if(isset($_POST['modify-user'])) {
            $oldStatus = $u->uStatus;
            $u->updateFromForm("post", "user-");
            $u->save();
            $groups = stringToArray($_POST['user-groups']);
            $u->setGroups($groups);
            echo UI::info("L'utilisateur a bien été mis à jour.");
        }
        ?>
            <fieldset>
                <legend><?php echo $u->toString(); ?></legend>
                <div class="form-group">
                    <label class="control-label col-md-4">Identifiant</label>
                    <div class="controls col-md-8">
                        <input class="form-control" type="text" name="user-uLogin" value="<?php echo $u->uLogin; ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">E-mail</label>
                    <div class="controls col-md-8">
                        <input class="form-control" type="text" name="user-uEmail" value="<?php echo $u->uEmail; ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">Nom d'affichage</label>
                    <div class="controls col-md-8">
                        <input class="form-control" type="text" name="user-uDisplayName" value="<?php echo $u->uDisplayName; ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">Groupes</label>
                    <div class="col-md-4">
                        <ul id="user-groups-list"></ul>
                    </div>
                    <div class="col-md-4">
                        <input type="text" autocomplete="off" id="groups-list" class="form-control" spellcheck="false" placeholder="Ajouter un groupe" />
                    </div>
                    <input type="hidden" name="user-groups" id="user-groups" value="<?php echo arrayToString($u->getGroups(), ""); ?>" />
                </div>
                <?php
                $displayPasswordChange = false;
                if(isset($_POST['new-password'])) {
                    $u->uPassword = crypt($_POST['new-password']);
                    $u->setOption("reset_password", true);
                    $u->save();
                    echo UI::info("Le mot de passe a bien été réinitialisé.");
                }
                ?>
                <div class="col-md-offset-4 col-md-4" <?php if($displayPasswordChange) echo "style=\"display:none;\""; ?>>
                    <button class="btn btn-default reset-password" type="button"><i class="fa fa-key"></i> Réinitialiser le mot de passe</button>
                </div>
                <div class="form-group reset-password" <?php if(!$displayPasswordChange) echo "style=\"display:none;\""; ?>>
                    <label class="control-label col-md-4">Nouveau mot de passe</label>
                    <div class="controls col-md-4">
                        <input class="form-control" type="password" name="new-password" />
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-default cancel-reset" type="button">Annuler</button>
                    </div>
                </div>
            </fieldset>
            <div class="form-actions">
                <div class="col-md-offset-4 col-md-8">
                    <button class="btn btn-primary" name="modify-user" type="submit"><i class="fa fa-save"></i> Modifier l'utilisateur</button>
                    <a class="btn btn-default" href="administration/users.html">Annuler</a>
                </div>
            </div>
        </form>
        </div>
        <div class="col-md-6">
        <fieldset>
            <legend>Actions</legend>
            <ul class="fa-ul">
                <li><a href="user/view/<?php echo $u->getId(); ?>" class="action"><i class="fa fa-eye fa-li"></i> Voir le profil de l'utilisateur</a></li>
            </ul>
        </fieldset>
        </div>
        </div>
        <?php
    }
    ?>
    <script type="text/javascript">
    function addGroup(group) {
        var array = stringToArray($("#user-groups").val());
        if(group !== undefined) {
            if($("#user-groups").val().search(group) == -1) {
                array.push(group);
            }
        }
        $("#user-groups-list").empty()
        for(var i in array.sort()) {
            $("#user-groups-list").append("<li>" + array[i] + "<i class=\"fa fa-trash action pull-right\"></i></li>");
        }
        $("#user-groups").val(arrayToString(array.sort(), ""))
        $("#groups-list").focus().typeahead("val", "");
        groupsActions();
    }
    
    function groupsActions() {
        $("#user-groups-list i").unbind("click");
        $("#user-groups-list i").click(function(){
            var group = $(this).parent().text();
            var array = stringToArray($("#user-groups").val());
            for(var i in array) {
                if(array[i] == group) {
                    array.splice(i, 1);
                }
            }
            $("#user-groups").val(arrayToString(array.sort(), ""))
            $(this).closest("li").remove();
        });
    }
    
    $(document).ready(function(){
        $(".events li").tooltip({placement: "left", html: true});
        $("[name=reset-password]").click(function(e){
            if(!confirm("Voulez-vous vraiment effectuer cette action ?")) {
                e.preventDefault();
            }
        });
        
        $("#groups-list").typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                name: "groups",
                displayKey: 'value',
                source: substringMatcher([<?php echo arrayToString($groupsArray, "'"); ?>])
            }).bind("typeahead:selected", function(){
            addGroup($("#groups-list").val());
            $("#groups-list").typeahead("setQuery", "")
        });
        
        disableOnEmpty($("[name=user-uLogin]"), $("button[type=submit]"));
        //Init groups list
        addGroup();
        
        $(".add-requested-groups").click(function(e){
            e.preventDefault();
            $(".requested-groups li.group").each(function(){
                addGroup($(this).text());
            });
        });
        
        $(".btn.reset-password").click(function(){
            $(".form-group.reset-password").show();
            $(this).parent().hide();
        });
        $(".btn.cancel-reset").click(function(){
            $(".form-group.reset-password").hide();
            $(".btn.reset-password").parent().show();
        });
    });
    var substringMatcher = function(strs) {
        return function findMatches(q, cb) {
        var matches, substrRegex;
        
        // an array that will be populated with substring matches
        matches = [];
        
        // regex used to determine if a string contains the substring `q`
        substrRegex = new RegExp(q, 'i');
        
        // iterate through the pool of strings and for any string that
        // contains the substring `q`, add it to the `matches` array
        $.each(strs, function(i, str) {
        if (substrRegex.test(str)) {
        // the typeahead jQuery plugin expects suggestions to a
        // JavaScript object, refer to typeahead docs for more info
        matches.push({ value: str });
        }
        });
        
        cb(matches);
        };
    };
    </script>
    <script type="text/javascript" src="assets/vendor/typeahead.js/dist/typeahead.jquery.min.js"></script>
    <?php
} else {
    //Delete user
    if(isset($_POST['selected-users'])) {
        foreach($_POST['selected-users'] as $userId) {
            $user = new User();
            $user->loadFromId($userId);
            $user->delete();
        }
        echo UI::info("" . (sizeof($_POST['selected-users']) == 1 ? "L'utilisateur a bien été supprimé." : "Les utilisateurs ont bien été supprimés."));
    }
    
    //Search
    $newSearch = false;
    if(!isset($_POST['search'])) {
        $_POST['search'] = str_replace("%", "", $_SESSION['adminUserSearchTerm']);
    } else {
        //New search, go back to page 1
        $_SESSION['adminUserPageNumber'] = 1;
        $newSearch = true;
    }
    if($_POST['search'] == "") {
        $_SESSION['adminUserSearchTerm'] = "%";
    } else {
        $_SESSION['adminUserSearchTerm'] = "%{$_POST['search']}%";
    }
    
    $linesPerPage = 15;
    
    //Page number
    if(isset($_GET['pageNumber']) && !$newSearch) {
        $_SESSION['adminUserPageNumber'] = $_GET['pageNumber'];
    }
    if($_SESSION['adminUserPageNumber'] == 0) {
        $_SESSION['adminUserPageNumber'] = 1;
    }
    
    $lowLimit = ($_SESSION['adminUserPageNumber'] - 1) * $linesPerPage;
    
    $database = new Database();
    $totalRequest = $database->prepare("SELECT COUNT(uId) AS total FROM " . ENV_TABLES_PREFIX . "users WHERE uDisplayName LIKE :searchTerm OR uLogin LIKE :searchTerm;");
    $totalRequest->execute(Array("searchTerm" => $_SESSION['adminUserSearchTerm']));
    $line = $totalRequest->fetch();
    $total = $line['total'];
    
    $request = $database->prepare("SELECT *, (SELECT COUNT(uId) FROM " . ENV_TABLES_PREFIX . "users) AS total FROM " . ENV_TABLES_PREFIX . "users WHERE uDisplayName LIKE :searchTerm OR uLogin LIKE :searchTerm ORDER BY uId ASC LIMIT $lowLimit, $linesPerPage;");
    $request->execute(Array("searchTerm" => $_SESSION['adminUserSearchTerm']));
    ?>
    <div class="header-menu">
        <form class="form-inline pull-right" method="post" style="width:300px;">
            <div class="col-md-12">
                <div class="input-group hidden">
                    <input type="text" class="form-control" name="search" value="<?php echo $_POST['search']; ?>" /> 
                    <span class="input-group-btn">
                        <button class="btn btn-default" href="#"><i class="fa fa-search"></i> Rechercher</button>
                    </span>
                </div>
            </div>
        </form>
        <form method="post" onsubmit="return confirm('Ces utilisateurs seront supprimés pour toujours. Voulez-vous vraiment effectuer cette action ?');">
        <div class="pull-left" id="selected-actions"><button type="submit" name="selected-action-delete" class="btn btn-danger" href="" disabled><i class="fa fa-user-times fa-fw"></i></button> <a href="administration/users/0" class="btn btn-default"><i class="fa fa-user-plus fa-fw"></i></a></div>
    </div>
    <?php
    if(isset($_POST['search']) && $_POST['search'] != "") {
        switch($total) {
            case 0:
                echo "<p><em>Aucun résultat</em></p>";
                break;
            case 1:
                echo "<p><em>1 résultat</em></p>";
                break;
            default:
                echo "<p><em>$total résultats</em></p>";
                break;
        }
    }
    ?>
    <table class="table table-condensed table-hover users">
        <thead>
            <tr><th style="width:1em;"></th><th>Nom d'affichage</th><th>Identifiant</th><th>Dernière connexion</th><th>Groupes</th><th style="width:30px;"></th></tr>
        </thead>
        <tbody>
        <?php
            while($line = $request->fetch()) {
                $u = new User();
                $u->loadFromRow($line);
                debug($u->uLogin . " - " . date("d/m/Y", $u->uLastLogin));
                echo "<tr><td><input type=\"checkbox\" name=\"selected-users[]\" value=\"{$u->getId()}\" /></td><td>{$u->uDisplayName}</td><td>{$u->uLogin}</td><td>" . timestampToRelative($u->uLastLogin) . "</td><td>" . (sizeof($u->getGroups()) ? arrayToString($u->getGroups(), "") : "<em>This user has no groups</em>") . "</td><td><a class=\"btn btn-xs btn-primary\" href=\"administration/users/{$u->getId()}\" title=\"Edit user information\"><i class=\"fa fa-edit\"></i></a></td></tr>";
            }
            if(!$request->rowCount()) {
                echo "<tr><td></td><td colspan=5><em>Aucun utilisateur trouvé</em></td></tr>";
            }
        ?>
        </tbody>
    </table>
    </form>
    <?php
    echo "<div class=\"centered\">";
    echo UI::pagination($_SESSION['adminUserPageNumber'], $total, $linesPerPage, "administration/users/page/%");
    echo "</div>";
    ?>
    <script type="text/javascript">
    $(document).ready(function(){
        $("table input[type=checkbox]").click(function(){
            $(this).parent().parent().toggleClass("checked");
            if($("table tr.checked").size()) {
                $("#selected-actions button").removeAttr("disabled");
            } else {
                $("#selected-actions button").attr("disabled", "disabled");
            }
        });
    });
    </script>
<?php
}
?>
