<form class="form-horizontal row" method="post">
    <div class="col-md-6">
        <fieldset>
            <legend>Informations personnelles</legend>
            <?php
            if(isset($_POST['changeDisplayName'])) {
                if(strlen($_POST['uDisplayName']) > 2) {
                    User::getAuth()->uDisplayName = $_POST['uDisplayName'];
                    User::getAuth()->save();
                    echo UI::info("Votre nom d'affichage a bien été modifié.");
                } else {
                    echo UI::error("<strong>Erreur</strong> Le nom d'affichage choisi doit faire au moins 3 caractères.");
                }
            }
            ?>
            <div class="form-group">
                <label class="control-label col-md-4">Identifiant</label>
                <div class="controls col-md-8">
                    <input class="input-large form-control input-flat" type="text" value="<?php echo User::getAuth()->uLogin; ?>" disabled />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4">Email</label>
                <div class="controls col-md-8">
                    <input class="input-large form-control input-flat" type="text" value="<?php echo User::getAuth()->uEmail; ?>" disabled />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4">Nom d'affichage</label>
                <div class="controls col-md-8">
                    <input class="input-large form-control input-flat" type="text" name="uDisplayName" value="<?php echo User::getAuth()->uDisplayName; ?>" autocomplete="off" />
                </div>
            </div>
            <div class="form-actions">
            <div class="col-md-4 col-md-offset-4">
                <button class="btn btn-primary" name="changeDisplayName" type="submit"><i class="fa fa-user"></i> Modifier mon nom d'affichage</button>
            </div>
        </div>
        </fieldset>
        <fieldset>
            <legend>Mot de passe</legend>
            <?php
            if(isset($_POST['changePassword'])) {
                if(crypt($_POST['password-current'], User::getAuth()->uPassword) == User::getAuth()->uPassword) {
                    if($_POST['password-new'] == $_POST['password-new-confirmation']) {
                        User::getAuth()->uPassword = crypt($_POST['password-new']);
                        User::getAuth()->save();
                        $expiration = time() + 60 * 60 * 24 * 30;
                        setcookie("login", User::getAuth()->uLogin, $expiration);
                        setcookie("password", User::getAuth()->uPassword, $expiration);
                        echo UI::info("Votre mot de passe a bien été mis à jour.</p>");
                    } else {
                        echo UI::error("<strong>Erreur</strong> Les nouveaux mots de passe sont différents");
                    }
                } else {
                    echo UI::error("<strong>Erreur</strong> Le mot de passe est incorrect");
                }
            }
            ?>
            <div class="form-group">
                <label class="control-label col-md-4">Mot de passe actuel</label>
                <div class="controls col-md-8">
                    <input class="input-large form-control" type="password" name="password-current" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4">Nouveau mot de passe</label>
                <div class="controls col-md-8">
                    <input class="input-large form-control" type="password" name="password-new" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4">Confirmer</label>
                <div class="controls col-md-8">
                    <input class="input-large form-control" type="password" name="password-new-confirmation" />
                </div>
            </div>
        </fieldset>
        <div class="form-actions">
            <div class="col-md-4 col-md-offset-4">
                <button class="btn btn-primary" name="changePassword" type="submit"><i class="fa fa-key"></i> Modifier mon mot de passe</button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <fieldset>
            <legend>Préférences</legend>
            <?php
            if(isset($_POST['changeSettings'])) {
                User::getAuth()->setOption("app_name", $_POST['pref_app_name']);
                User::getAuth()->setOption("start_page", $_POST['pref_start_page']);
                User::getAuth()->save();
                echo UI::info("Vos préférences ont bien été mises à jour.");
            }
            ?>
            <div class="form-group">
                <label class="control-label col-md-4">Page d'accueil</label>
                <div class="controls col-md-8">
                    <?php
                    $sP = Array("home.html" => "Résumé des comptes");
                    $accounts = User::getAuth()->getAccounts();
                    foreach($accounts as $account) {
                        $sP["accounts/view/" . $account->getId()] = "Compte - {$account->aName}";
                    }
                    echo UI::select("pref_start_page", $sP, User::getAuth()->getOption("start_page"));
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-4">Nom de l'application</label>
                <div class="controls col-md-8">
                    <input class="form-control" name="pref_app_name" value="<?php echo User::getAuth()->getOption("app_name") ? User::getAuth()->getOption("app_name") : "Homerun"; ?>" />
                </div>
            </div>
        </fieldset>
        <div class="form-actions">
            <div class="col-md-8 col-md-offset-4">
                <button class="btn btn-primary" name="changeSettings" type="submit"><i class="fa fa-save"></i> Modifier</button>
            </div>
        </div>
    </div>
</form>
