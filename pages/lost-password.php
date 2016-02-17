<div class="well well-sm col-md-6 col-md-offset-3">
    <form class="form-horizontal" method="post">
        <h3 class="title">Lost password</h3>
        <?php
        if(isset($_POST['email'])) {
            $user = new User();
            if($user->loadFromUniqueField("uEmail", $_POST['email'])) {
                $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
                $pass = array();
                $alphaLength = strlen($alphabet) - 1;
                for($i = 0; $i < 15; $i++) {
                    $n = rand(0, $alphaLength);
                    $pass[] = $alphabet[$n];
                }
                $newPass = implode($pass);
                $user->set("uPassword", crypt($newPass));
                $user->setOption("reset_password", true);
                $user->save();
                $mail = new Mail($user->uEmail);
                $mail->setSubject("Homerun lost password");
                $mail->setTitle("Homerun lost password");
                $mail->setContent("<p>Votre mot de passe temporaire est</p><p style=\"font-family:monospace;text-align:center;padding:0;margin:0;\" onclick=\"this.select();\">----------$newPass----------</p><p><a href=\"" . ENV_BASE_URL ."/login.html\">Merci de vous connecter à Homerun</a> en utilisant votre identifiant et ce mot de passe temporaire (ne pas copier les tirets). Vous devrez immédiatement réinitialiser votre mot de passe une fois connecté.</p>");
                $mail->send();
                echo UI::info("Un nouveau mot de passe vous a été envoyé par e-mail. Merci de suivre les instructions qu'il contient pour vous <a href=\"login.html\">connecter</a>.");
            } else {
                echo UI::error("Cet e-mail n'est associé à aucun compte.");
                echo "<p class=\"centered\"><a class=\"btn btn-default\" href=\"lost-password.html\">Revenir</a></p>";
            }
        } else {
            echo UI::info("Si vous avez perdu votre mot de passe, merci de remplir le formulaire ci-dessous. Un mot de passe temporaire vous sera envoyé par e-mail, utilisez-le pour vous connecter et il vous sera automatiquement demandé de le réinitialiser.");
            ?>
            <div class="form-group">
                <label class="control-label col-md-3">E-mail</label>
                <div class="controls col-md-5">
                    <input type="text" class="form-control" name="email" />
                </div>
            </div>
            <div class="form-actions">
                <div class="col-md-offset-3 col-md-9">
                    <button class="btn btn-primary">Demander un nouveau mot de passe</button>
                    <a class="btn btn-default" href="login.html">Annuler</a>
                </div>
            </div>
            <?php
        }
        ?>
    </form>
</div>
