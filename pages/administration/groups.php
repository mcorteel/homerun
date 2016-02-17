<?php
if(isset($_GET['id'])) {
    $g = new Group();
    $g->loadFromId($_GET['id']);
    //Group information modification
    if(isset($_POST["modify"])) {
        $g->updateFromForm("post", "group-");
        $g->save();
        echo UI::info("Le groupe a bien été mis à jour.");
        $database = new Database();
        $request = $database->prepare("UPDATE " . ENV_TABLES_PREFIX . "members SET mADmin = 0 WHERE mGroupId = :gId;");
        $request->execute(Array("gId" => $g->getId()));
        if(is_array($_POST["member"])) {
            $request = $database->prepare("UPDATE " . ENV_TABLES_PREFIX . "members SET mADmin = 1 WHERE mGroupId = :gId AND mUserId IN (" . arrayToString($_POST["member"], "") . ");");
            $request->execute(Array("gId" => $g->getId()));
        }
    }
    //Group type modification
    if(isset($_POST['change-group-type'])) {
        $g->gSystem = !$g->gSystem;
        $g->save();
    }
    ?>
    <div class="row">
    <form class="form-horizontal col-md-6" method="post">
        <fieldset>
            <legend>Général</legend>
            <div class="form-group">
                <label class="control-label col-md-4">Nom</label>
                <div class="controls col-md-4">
                    <input class="form-control" type="text" name="group-gName" value="<?php echo $g->gName; ?>" <?php if($g->gSystem) echo "disabled"; ?>/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4">Création</label>
                <div class="controls col-md-4">
                    <input class="form-control" type="text" name="group-gCreationDate" value="<?php echo date("d/m/Y", $g->gCreationDate); ?>" disabled />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4">Administrateurs</label>
                <div class="col-md-8">
                <ul class="list-unstyled">
                <?php
                foreach($g->getMembers() as $member) {
                    echo "<li class=\"member checkbox\"><label><input type=\"checkbox\" name=\"member[]\" value=\"{$member->getId()}\" " . ($member->isAdminOf($g->gName) ? "checked" : "") . " /> {$member->uDisplayName}</label></li>";
                }
                if(!sizeof($g->getMembers())) {
                    echo "<div class=\"controls\"><em>Ce groupe ne contient aucun membre</em></div>";
                }
                ?>
                </ul>
                </div>
            </div>
        </fieldset>
        <div class="form-actions">
            <div class="col-md-8 col-md-offset-4">
                <button class="btn btn-primary" name="modify" type="submit"><i class="fa fa-save"></i> Modifier</button>
                <a class="btn btn-default" href="administration/groups.html">Annuler</a>
            </div>
        </div>
    </form>
    <form class="col-md-6" method="post">
        <fieldset>
            <legend>Actions</legend>
            <?php
            if(!$g->gSystem) {
                echo "<button class=\"btn btn-default change-confirm\" name=\"change-group-type\"><i class=\"fa fa-cogs\"></i> Passer en groupe système</button>";
            }
            //NOTE: cannot change back from system group for security reasons
            ?>
        </fieldset>
    </form>
    </div>
    <script type="text/javascript">
    $(document).ready(function(){
        disableOnEmpty($("[name=group-gName]"), $("button[type=submit]"));
        $(".change-confirm").click(function(e){
            if(!confirm("Souhaitez-vous vraiment transformer ce groupe en groupe système ? Cette action est irréversible. Ne le faites pas à moins de savoir ce que ça implique...")) {
                e.preventDefault();
            }
        });
    });
    </script>
    <?php
} else {
    if(isset($_POST['create-name'])) {
        if(preg_match("#^[A-Za-z0-9_-]{3,}#", $_POST['create-name'])) {
            //TODO: should check if group name already exists
            $group = new Group();
            $group->gName = $_POST['create-name'];
            $group->gSystem = 0;
            $group->gCreationDate = time();
            $group->create();
            echo UI::info("Le groupe a bien été créé.");
        } else {
            echo UI::error("Ce nom de groupe n'est pas valable.");
        }
    }
    if(isset($_POST['selected-action-delete'])) {
        foreach($_POST["selected-groups"] as $groupId) {
            $group = new Group();
            $group->loadFromId($groupId);
            $group->delete();
        }
        echo UI::info("Les groupes sélectionnés ont bien été supprimés.");
    }
    ?>
    <div class="header-menu">
        <form class="form-inline pull-right" method="post" style="width:300px;"><div class="input-group"><input class="form-control" type="text" name="create-name" id="create-name" /><div class="input-group-btn"><button class="btn btn-default" id="create-group" disabled><i class="fa fa-plus"></i> Créer un groupe</button></div></div></form>
    <form method="post">
        <div class="pull-left" id="selected-actions"><button type="submit" name="selected-action-delete" class="btn btn-danger" href="" disabled><i class="fa fa-trash-o"></i> Supprimer</button></div>
    </div>
    <table class="table table-condensed table-hover">
        <thead>
            <tr><th style="width:1em;"></th><th style="width:100px;">Nom</th><th>Membres</th><th style="width:200px;">Création</th><th style="width:25px;"></th></tr>
        </thead>
        <tbody>
        <?php
        $database = new Database();
        $request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "groups ORDER BY gSystem, gName ASC");
        $request->execute();
        while($line = $request->fetch()) {
            $group = new Group();
            $group->loadFromRow($line);
            echo "<tr><td><input type=\"checkbox\" name=\"selected-groups[]\" value=\"{$group->getId()}\" /></td><td>{$group->gName}</td><td>" . (sizeof($group->getMembers()) ? arrayToString($group->getMembers(), "") : "<em>Ce groupe ne contient aucun membre</em>") . "</td><td>" . timestampToFancy($group->gCreationDate) . "</td><td><a class=\"btn btn-xs btn-primary\" href=\"administration/groups/{$group->getId()}\" title=\"Modifier ce groupe\"><i class=\"fa fa-edit\"></i></a></td></tr>";
        }
        ?>
        </tbody>
    </table>
    </form>


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
        disableOnEmpty($("#create-name"), $("#create-group"));
    });
    </script>
<?php
}
?>
