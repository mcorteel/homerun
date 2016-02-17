<?php
if(User::getAuth()) {
    if(isset($_GET['id'])) {
        $group = new Group();
        if($group->loadFromId($_GET['id'])) {
            ?>
            <h2><i class="fa fa-group"></i> <?php echo $group->gName; ?></h2>
            <div class="well">
                <h3>Informations générales</h3>
                <p>Ce groupe a été créé <?php echo timestampToFancy($group->gCreationDate); ?>.</p>
                <h3>Membres</h3>
                <ul class="members fa-ul">
                <?php
                foreach($group->getMembers() as $member) {
                    echo "<li class=\"user\"><a href=\"user/view/{$member->getId()}\"><i class=\"fa fa-user fa-li\"></i>{$member->toString()}</a></li>";
                }
                ?>
                </ul>
                <h3>Administrateurs</h3>
                <ul class="members fa-ul">
                <?php
                foreach($group->getAdmins() as $admin) {
                    echo "<li class=\"user\"><a href=\"user/view/{$admin->getId()}\"><i class=\"fa fa-user fa-li\"></i>{$admin->toString()}</a></li>";
                }
                if(!sizeof($group->getAdmins())) {
                    echo "<li class=\"nothing\">Ce groupe n'a pas d'administrateurs</li>";
                }
                ?>
                </ul>
                <?php
                if($group->isAdmin(User::getAuth())) {
                    ?>
                    <h3>Actions</h3>
                    <a href="account/groups/<?php echo $group->getId(); ?>" class="btn btn-primary">Gérer ce groupe</a>
                    <?php
                }
                ?>
            </div>
            <?php
        } else {
            echo UI::error("Ce groupe n'existe pas");
        }
    } else {
        echo UI::error("Missing group id");
    }
} else {
    header("Location: " . ENV_BASE_URL . "/home.html");
}
?>
