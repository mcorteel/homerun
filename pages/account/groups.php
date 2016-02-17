<?php
if(isset($_GET['id'])) {
    $group = new Group();
    $group->loadFromId($_GET['id']);
    if(User::getAuth()->isAdminOf($group->gName)) {
        ?>
        <form class="form-horizontal" method="post">
            <div class="header-menu">
                <button type="submit" class="btn btn-default make-admin" name="make-admin" disabled>Admin</button>
                <button type="submit" class="btn btn-danger revoke" name="revoke" disabled><i class="fa fa-user-times fa-fw"></i></button>
                <a href="account/groups.html" class="btn btn-default pull-right"><i class="fa fa-arrow-left"></i></a>
                <h3 class="title">Administration du groupe « <?php echo $group->gName; ?> »</h3>
            </div>
            <?php
            if(isset($_POST['member'])) {
                $member = new User();
                $member->loadFromId($_POST['member']);
                if(isset($_POST['revoke'])) {
                    if(User::getAuth()->getId() != $member->getId()) {
                        $member->removeGroup($group->getId());
                        echo UI::info("{$member->toString()} n'est plus membre du groupe");
                    } else {
                        echo UI::error("Impossible de vous retirer vous même du groupe");
                    }
                } elseif(isset($_POST['make-admin'])) {
                    if(User::getAuth()->getId() != $member->getId()) {
                        if($member->isAdminOf($group->gName)) {
                            $database = new Database();
                            $request = $database->prepare("UPDATE members SET mADmin = 0 WHERE mGroupId = :gId AND mUserId = :uId;");
                            $request->execute(Array("gId" => $group->getId(), "uId" => $member->getId()));
                            echo UI::info("{$member->toString()} n'est plus administrateur de ce groupe");
                        } else {
                            $database = new Database();
                            $request = $database->prepare("UPDATE members SET mADmin = 1 WHERE mGroupId = :gId AND mUserId = :uId;");
                            $request->execute(Array("gId" => $group->getId(), "uId" => $member->getId()));
                            echo UI::info("{$member->toString()} est maintenant administrateur de ce groupe");
                        }
                    } else {
                        echo UI::error("Impossible de changer ce réglage pour vous-même");
                    }
                }
            }
            ?>
            <table class="table table-condensed group-members">
                <thead><tr><th class="hidden"></th><th>Nom</th><th>Administrateur du groupe</th></tr></thead>
                <tbody>
                <?php
                $members = $group->getMembers(true);
                foreach($members as $member) {
                    echo "<tr><td class=\"hidden\"><input type=\"radio\" name=\"member\" value=\"{$member->getId()}\" /></td><td class=\"name\">{$member->toString()}</td><td class=\"admin\">" . ($group->isAdmin($member) ? "Oui" : "Non") .  "</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </form>
        <script>
        $(document).ready(function(){
            $(".table tbody tr").click(function(){
                $(this).find("input").prop("checked", !$(this).find("input").prop("checked"));
                $(this).closest("tbody").find("tr").removeClass("selected");
                $(this).closest("tbody").find("input:checked").closest("tr").addClass("selected");
                $(".header-menu .btn").prop("disabled", !$("[name=member]:checked").size());
                if($("[name=member]:checked").size()) {
                    var admin = ($("[name=member]:checked").closest("tr").find("td.admin").text() == "Oui");
                    $(".make-admin").toggleClass("no", admin);
                }
            });
            
            $(".revoke").click(function(){
                if(!confirm("Voulez-vous vraiment effectuer cette action ?")) {
                    return false;
                }
            });
        });
        </script>
        <?php
    } else {
        echo UI::error("Vous n'avez pas les droits nécessaires pour gérer ce groupe");
    }
} else {
?>
    <table class="table table-condensed table-hover">
        <thead>
            <tr><th style="width:70px;"></th><th>Nom</th><th>Membres</th><th>Administrateur</th></tr>
        </thead>
        <tbody>
            <?php
            $groups = User::getAuth()->getGroups(true);
            foreach($groups as $group) {
                echo "<tr><td>" . (User::getAuth()->isAdminOf($group->gName) ? "<a href=\"account/groups/{$group->getId()}\" title=\"Gérer ce groupe\" class=\"btn btn-primary btn-xs\"><i class=\"fa fa-wrench fa-fw\"></i></a> " : "") . " <a href=\"group/view/{$group->getId()}\" class=\"btn btn-primary btn-xs\" title=\"Voir la fiche du groupe\"><i class=\"fa fa-eye fa-fw\"></i></a></td><td>{$group->gName}</td><td>" . (sizeof($group->getMembers()) ? arrayToString($group->getMembers(), "") : "<em>Ce groupe ne contient aucun membre</em>") . "</td><td>" . (User::getAuth()->isAdminOf($group->gName) ? "Oui" : "Non") . "</td></tr>";
            }
            if(!sizeof(User::getAuth()->getGroups())) {
                echo "<tr><td colspan=4>" . UI::info("Vous n'êtes membre d'aucun groupe") . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    $groups = Array();
    foreach(User::getAuth()->getGroups() as $group) {
        if(User::getAuth()->isAdminOf($group->gName)) {
            $groups[] = $group;
        }
    }
}
?>
