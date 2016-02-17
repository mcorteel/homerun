<?php
//Redirection for connected users
if(User::getAuth()) {
    header("Location:home.html");
}
?>
<div class="col-md-12">
<h3 class="title">Connexion</h3>
<?php
if(isset($_POST['login-login'])) {
    $authUser = new User();
    $database = new Database();
    $errors = "";
    if($request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "users WHERE uLogin = :login;")) {
        if($request->execute(Array("login" => $_POST['login-login']))) {
            if($line = $request->fetch()) {
                if(crypt($_POST['login-password'], $line['uPassword']) == $line['uPassword']) {
                    if($line['uStatus'] < 1) {
                        $errors = "Your account is not yet validated. You should get an email from an administrator when it has been validated.";
                    }
                    $authUser->loadFromRow($line);
                    $authUser->uLastLogin = time();
                    $authUser->save();
                    $_SESSION['authUser'] = $authUser;
                    $expiration = time() + 60 * 60 * 24 * 30;
                    setcookie("login", $authUser->uLogin, $expiration);
                    setcookie("password", $authUser->uPassword, $expiration);
                    header("Location:/");
                } else {
                    $errors = "Your login and password don't match, please try again.";
                }
            } else {
                $errors = "Your login and password don't match, please try again.";
            }
        } else {
            error("cannot execute request - " . print_r($request->errorInfo(), true));
        }
    } else {
        error("cannot prepare request - " . print_r($database->errorInfo(), true));
    }
    if($errors) {
        echo UI::warning("$errors");
    }
}
?>
<form class="form-horizontal" method="post">
    <div class="form-group">
        <label class="control-label col-md-4">Identifiant</label>
        <div class="controls col-md-6">
            <input class="form-control" type="text" name="login-login" value="<?php echo $_POST['login-login']; ?>" />
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-4">Mot de passe</label>
        <div class="controls col-md-6">
            <input class="form-control" type="password" name="login-password" <?php if(isset($_POST['login-login'])) echo "autofocus"; ?>/>
        </div>
    </div>
    <div class="form-actions">
        <div class="col-md-offset-4 col-md-8">
            <button class="btn btn-primary" type="submit">Connexion</button>
            <a class="btn btn-link" href="lost-password.html">Mot de passe oublié</a>
        </div>
    </div>
</form>
</div>
