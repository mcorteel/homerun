<?php
if(User::getAuth()) {
    if(isset($_GET['subPage'])) {
        $subPage = $_GET['subPage'];
    } else {
        $subPage = "general";
    }
    ?>
    <h2>Préférences</h2>
    <ul class="nav nav-tabs" id="myTab" style="margin-bottom:0;padding-right:0;">
        <li class="<?php if($subPage == "general")echo "active"; ?>"><a href="account/general.html"><i class="fa fa-user"></i> Mon compte</a></li>
        <li class="<?php if($subPage == "groups")echo "active"; ?>"><a href="account/groups.html"><i class="fa fa-group"></i> Mes groupes</a></li>
    </ul>
    <div class="tab-content">
        <?php
        include_once("pages/account/$subPage.php");
        ?>
    </div>
<?php
} else {
    header("Location: " . ENV_BASE_URL . "/home.html");
}
?>
