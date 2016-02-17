<?php
if(!User::getAuth() || !User::getAuth()->hasGroup("admin")) {
    echo UI::error("This page is only available to administrators");
} else {
    if(isset($_GET['subPage'])) {
        $subPage = $_GET['subPage'];
    } else {
        $subPage = "users";
    }
    ?>
    <h2>Administration</h2>
    <ul class="nav nav-tabs" id="myTab" style="margin-bottom:0;padding-right:0;">
        <li class="<?php if($subPage == "users")echo "active"; ?>"><a href="administration/users.html">Utilisateurs</a></li>
        <li class="<?php if($subPage == "groups")echo "active"; ?>"><a href="administration/groups.html">Groupes</a></li>
    </ul>
    <div class="tab-content">
        <?php
        include_once("pages/administration/$subPage.php");
        ?>
    </div>
<?php
}
?>
