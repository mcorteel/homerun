<?php
/*****************************************************************************
 * Copyright 2013-2016 Maxime Corteel                                        *
 *                                                                           *
 * This file is part of Homerun                                              *
 *                                                                           *
 * Homerun is free software: you can redistribute it and/or                  *
 * modify it under the terms of the GNU Affero General Public License as     *
 * published by the Free Software Foundation, either version 3 of the        *
 * License, or (at your option) any later version.                           *
 *                                                                           *
 * This program is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             *
 * GNU Affero General Public License for more details.                       *
 *                                                                           *
 * You should have received a copy of the GNU Affero General Public License  *
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.     *
 *****************************************************************************/

ob_start();

include_once("include/utilities.php");
include_once("include/environment.php");
include_once("include/autoload.php");

if(isset($_GET['page'])) {
    $currentPage = $_GET['page'];
} else if(User::getAuth()) {
    $sP = User::getAuth()->getOption("start_page");
    if($sP) {
        header("Location: /$sP");
    } else {
        header("Location: /home.html");
    }
}

if(!User::getAuth()) {
    $currentPage = "login";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Comptes</title>
        <base href="<?php echo ENV_BASE_URL; ?>/">
        <link rel="icon" type="image/png" href="<?php echo ENV_BASE_URL; ?>/img/favicon.png">
        <script type="text/javascript">
        var ENV_BASE_URL = "<?php echo ENV_BASE_URL; ?>";
        </script>
        <link rel="stylesheet" type="text/css" href="assets/vendor/bootstrap/dist/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="assets/css/typeahead.css">
        <link rel="stylesheet" type="text/css" href="assets/vendor/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="assets/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
        <link rel="stylesheet" type="text/css" href="assets/css/index.css">
        
        <link rel="stylesheet" type="text/css" href="assets/css/<?php echo $currentPage;?>.css">
        <script type="text/javascript" src="assets/vendor/jquery/dist/jquery.min.js"></script>
        <script type="text/javascript" src="assets/vendor/bootstrap/dist/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="assets/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="assets/js/bootstrap-datepicker.fr.min.js" charset="UTF-8"></script>
        <script type="text/javascript" src="assets/js/date.js"></script>
        <script type="text/javascript" src="assets/js/utilities.js"></script>
        <script type="text/javascript" src="assets/js/index.js"></script>
    </head>
    <body>
    <?php
    if(User::getAuth() && User::getAuth()->getOption("reset_password") == true)
    {
    ?>
        <div class="modal" style="display:block;"><div class="modal-dialog"><div class="modal-content">
            <form class="form-horizontal" method="post">
            <div class="modal-header"><h3>Changement de mot de passe</h3></div>
            <div class="modal-body">
                <?php
                $displayForm = true;
                if(isset($_POST['change-password'])) {
                    if(crypt($_POST['current-password'], User::getAuth()->uPassword) == User::getAuth()->uPassword) {
                        if($_POST['new-password'] == $_POST['repeat-password']) {
                            User::getAuth()->uPassword = crypt($_POST['new-password']);
                            echo UI::info("Votre nouveau mot de passe a bien été enregistré.");
                            $displayForm = false;
                            User::getAuth()->removeOption("reset_password");
                            User::getAuth()->save();
                        } else {
                            echo UI::error("Les nouveaux mots de passe ne correspondent pas.");
                        }
                    } else {
                        echo UI::error("Le mot de passe actuel est incorrect.");
                    }
                }
                if(isset($_POST['logout'])) {
                    include("pages/logout.php");
                }
                if($displayForm) {
                ?>
                <p>Il s'agit de votre première connexion ou votre mot de passe a été réinitialisé par un administrateur. Pour des raisons de sécurité, vous devez donc changer de mot de passe.</p>
                <div class="form-group">
                    <label class="control-label col-md-4">Mot de passe actuel</label>
                    <div class="controls col-md-8">
                        <input type="password" class="form-control" name="current-password">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">Nouveau mot de passe</label>
                    <div class="controls col-md-8">
                        <input type="password" class="form-control" name="new-password">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-4">Répéter le nouveau mot de passe</label>
                    <div class="controls col-md-8">
                        <input type="password" class="form-control" name="repeat-password">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" name="change-password"><i class="fa fa-key"></i> Changer le mot de passe</button>
                <button type="submit" class="btn btn-default" name="logout"><i class="fa fa-power-off"></i> Déconnexion</button>
            </div>
            <?php
            } else {
            ?>
            <p style="text-align:center;"><a href="home.html" class="btn btn-primary"><i class="fa fa-refresh"></i> Recharger la page</a></p>
            </div>
            <?php
            }
            ?>
            </form>
        </div></div></div>
    <?php
    } else {
    ?>
        <!-- Main menu -->
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">Afficher la navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="home.html"><?php echo User::getAuth() && User::getAuth()->getOption("app_name") ? User::getAuth()->getOption("app_name") : "Homerun"; ?></a>
                <div id="main-info" class="alert alert-info"></div>
            </div>
            <div class="collapse navbar-collapse" id="navbar">
                <ul class="nav navbar-nav">
                    <?php
                    if(User::getAuth()) {
                        echo "<li" . ($_GET['page'] == "home" ? " class=\"active\"" : "") . "><a href=\"home.html\"><i class=\"fa fa-home fa-fw\"></i> Accueil</a></li>";
                        $accounts = User::getAuth()->getAccounts(true);
                        $accountsToggle = is_array(User::getAuth()->getOption("home_accounts_toggle")) ? User::getAuth()->getOption("home_accounts_toggle") : Array();
                        foreach($accounts as $account) {
                            echo "<li" . ($_GET['page'] == "accounts" && $_GET['subPage'] == "view" && $_GET['id'] == $account->getId() ? " class=\"active\"" : "") . " data-account-id=\"{$account->getId()}\"" . ($accountsToggle[$account->getId()] != "0" ? "" : " style=\"display:none;\"") . "><a href=\"accounts/view/{$account->getId()}\"><i class=\"fa fa-{$account->aIcon} fa-fw\"></i> {$account->aName}</a></li>";
                        }
                        echo "<li" . ($_GET['page'] == "statistics" ? " class=\"active\"" : "") . "><a href=\"statistics.html\"><i class=\"fa fa-bar-chart-o fa-fw\"></i> Statistiques</a></li>";
                        echo "<li" . ($_GET['page'] == "lists" ? " class=\"active\"" : "") . "><a href=\"lists.html\"><i class=\"fa fa-list fa-fw\"></i> Listes</a></li>";
                    }
                    ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    if(User::getAuth()) {
                        ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user fa-fw"></i> <?php echo User::getAuth()->uDisplayName; ?> <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="account.html"><i class="fa fa-fw fa-wrench"></i> Préférences</a></li>
                                <?php if(User::getAuth()->hasGroup("admin")){ ?>
                                <li><a href="administration.html"><i class="fa fa-fw fa-cogs"></i> Administration</a></li>
                                <?php } ?>
                                <li class="divider"></li>
                                <li><a href="logout.html"><i class="fa fa-fw fa-power-off"></i> Déconnexion</a></li>
                            </ul>
                        </li>
                        <?php
                    } else {
                        ?><li><a href="login.html">Connexion</a></li><?php
                    }
                    ?>
                </ul>
            </div>
        </nav>
        <!-- Main content -->
        <div class="main-container">
            <div class="container">
            <?php
            include_once("pages/$currentPage.php");
            ?>
            </div>
        </div>
        <!-- Modals -->
        <div id="modal-debug">
            <i class="fa fa-times close"></i>
            <i class="fa fa-ban clear"></i>
            <ul>
                <?php echo $_DEBUG; ?>
            </ul>
        </div>
    <?php
    }
    ?>
    </body>
</html>
<?php
ob_end_flush();
