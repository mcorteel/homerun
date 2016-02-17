<?php
if(User::getAuth())
{
    if(isset($_GET['id']))
    {
        $user = new User();
        if($user->loadFromId($_GET['id']))
        {
            ?>
            <h2><i class="fa fa-group"></i> <?php echo $user->uDisplayName; ?></h2>
            <div class="well">
                <h3>Groupes</h3>
                <ul class="groups fa-ul">
                <?php
                foreach($user->getGroups() as $group) {
                    echo "<li><a href=\"group/view/{$group->getId()}\"><i class=\"fa fa-group fa-li\"></i>{$group->gName}</a>" . ($user->isAdminOf($group->gName) ? " (administrateur)" : "") . "</li>";
                }
                ?>
                </ul>
                <?php
                if(User::getAuth()->hasGroup("admin")) {
                    ?>
                    <h3>Actions</h3>
                    <ul class="actions fa-ul">
                        <?php
                        echo "<li class=\"action\"><a href=\"administration/users/{$user->getId()}\"><i class=\"fa fa-edit fa-li\"></i>Voir dans l'administration</a></li>";
                        ?>
                    </ul>
                    <?php
                }
                ?>
            </div>
            <?php
        } else {
            echo UI::error("Cet utilisateur n'existe pas"    );
        }
    } else {
        echo UI::error("Identifiant d'utilisateur manquant");
    }
} else {
    header("Location: " . ENV_BASE_URL . "/home.html");
}
?>
