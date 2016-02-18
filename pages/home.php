<?php
if(!User::getAuth()) {
    header("Location: /login.html");
} else {
    ?>
    <div class="row">
        <div class="col-md-7">
            <h3>Tableau de bord</h3>
            <?php
            $database = new Database();
            $groups = Array();
            foreach(User::getAuth()->getGroups() as $group) {
                array_push($groups, $group->getId());
            }
            $request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "accounts WHERE aGroup IN (" . arrayToString($groups, "") . ") ORDER BY aName ASC");
            $request->execute();
            $accountsToggle = is_array(User::getAuth()->getOption("home_accounts_toggle")) ? User::getAuth()->getOption("home_accounts_toggle") : Array();
            $createConf = false;
            $hotkey = 1;
            while($line = $request->fetch()) {
                $account = new Account();
                $account->loadFromRow($line);
                if(!array_key_exists($account->getId(), $accountsToggle)) {
                    $accountsToggle[$account->getId()] = 1;
                    $createConf = true;
                }
                echo "<h4><a href=\"accounts/view/{$account->getId()}\">{$account->aName}</a><span class=\"hotkey\">(" . $hotkey++ . ")</span><span class=\"btn-group pull-right\">" . ($account->isLog() ? "" : "<a class=\"action btn btn-default btn-xs\" href=\"accounts/transfers/{$account->getId()}\"><i class=\"fa fa-exchange\"></i> Transferts</a>") . "<button class=\"btn btn-info btn-xs toggle\" data-id=\"{$account->getId()}\"><i class=\"fa fa-" . ($accountsToggle[$account->getId()] ? "minus" : "plus") .  "\"></i></button></span></h4>";
                ?>
                <div class="account"<?php if(!$accountsToggle[$account->getId()]) echo " style=\"display:none;\""; echo " data-account-id=\"{$account->getId()}\"" ?>>
                    <table class="table table-condensed table-bordered"><thead><tr>
                    <?php
                    if(!$account->isLog()) {
                        echo "<th></th>";
                    }
                    $req = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "tags WHERE tAccount = :account ORDER BY tId ASC;");
                    $req->execute(Array("account" => $account->getId()));
                    $tags = Array();
                    $sums = Array();
                    $transfers = Array();
                    while($row = $req->fetch()) {
                        echo "<th>{$row['tName']}</th>";
                        $tags[$row['tId']] = $row['tName'];
                    }
                    echo "</tr></thead><tbody>";
                    if($account->isLog()) {
                        $req = $database->prepare("SELECT SUM(iAmount) AS sum, iType FROM " . ENV_TABLES_PREFIX . "inputs WHERE iAccount = :account GROUP BY iType;");
                        $req->execute(Array("account" => $account->getId()));
                        while($row = $req->fetch()) {
                            $sums[$row['iType']] = $row['sum'];
                        }
                        foreach($tags as $i => $tag) {
                            if(!$sums[$i]) {
                                $sums[$i] = 0;
                            }
                        }
                        //Displaying inputs
                        foreach($sums as $type => $sum) {
                            echo "<td title=\"'{$tags[$type]}'\">" . toEuros($sum) . "</td>";
                        }
                        echo "</tr>";
                    } else {
                        foreach($account->getGroup()->getMembers() as $user) {
                            echo "<tr><th>{$user->uDisplayName}</th>";
                            $sums[$user->getId()] = Array();
                            foreach($tags as $id => $tag) {
                                $sums[$user->getId()][$id] = 0;
                            }
                            //Storing inputs
                            $req = $database->prepare("SELECT SUM(iAmount) AS sum, iType FROM " . ENV_TABLES_PREFIX . "inputs WHERE iAccount = :account AND iUser = :uId GROUP BY iType;");
                            $req->execute(Array("account" => $account->getId(), "uId" => $user->getId()));
                            while($row = $req->fetch()) {
                                $sums[$user->getId()][$row['iType']] = $row['sum'];
                            }
                            //Storing transfers
                            $req = $database->prepare("SELECT SUM(tAmount) AS sum FROM " . ENV_TABLES_PREFIX . "transfers WHERE tSender = :uId AND tAccount = {$account->getId()};");
                            $req->execute(Array("uId" => $user->getId()));
                            while($row = $req->fetch()) {
                                $transfers[$user->getId()]["out"] = $row['sum'];
                            }
                            $req = $database->prepare("SELECT SUM(tAmount) AS sum FROM " . ENV_TABLES_PREFIX . "transfers WHERE tReceiver = :uId AND tAccount = {$account->getId()};");
                            $req->execute(Array("uId" => $user->getId()));
                            while($row = $req->fetch()) {
                                $transfers[$user->getId()]["in"] = $row['sum'];
                            }
                            //Displaying inputs
                            foreach($sums[$user->getId()] as $type => $sum) {
                                echo "<td title=\"'{$tags[$type]}', par {$user->uDisplayName}\">" . toEuros($sum) . "</td>";
                            }
                            echo "</tr>";
                        }
                    }
                    echo "</tbody></table>";
                    if($account->isLog() && $account->aLimit > 0) {
                        $s = 0;
                        foreach($sums as $sum) {
                            $s += $sum;
                        }
                        echo "<div class=\"progress\"><div class=\"progress-bar progress-bar-" . ($s >= $account->aLimit ? "danger" : ($s >= 0.8 * $account->aLimit ? "warning" : "success")) . "\" style=\"width:" . str_replace(",", ".", $s / $account->aLimit * 100) . "%\"><span>" . round($s / $account->aLimit * 100) . "%</span></div></div>";
                    } else {
                        $max = 0;
                        $max2 = 0;
                        $sum2 = 0;
                        foreach($sums as $uId => $array) {
                            $sum = array_sum($array) - $transfers[$uId]["in"] + $transfers[$uId]["out"];
                            $sum2 += array_sum($array);
                            $max = $sum > $max ? $sum : $max;
                        }
                        debug("Total: $sum2");
                        debug("Maximum: $max");
                        foreach($account->getGroup()->getMembers() as $user)
                        {
                            $sum = array_sum($sums[$user->getId()]) - $transfers[$user->getId()]["in"] + $transfers[$user->getId()]["out"];
                            echo "<div class=\"progress\"><div class=\"progress-bar progress-bar-" . ($sum >= $max ? "success" : "warning") . "\" style=\"width:" . str_replace(",", ".", $sum / $max * 100) . "%\"><span>{$user->uDisplayName} " . ($sum >= $max ? "" : "<span class=\"pull-right\">-" . toEuros($max - $sum) . "</span>") . "</span></div></div>";
                        }
                    }
                echo "</div>";
                echo "<hr>";
            }
            
            if(!$request->rowCount()) {
                echo "<p class=\"alert alert-info\">Ce panneau affiche un résumé de vos compte. <a href=\"accounts/manage.html\">Cliquez ici pour créer un compte.</a></p>";
            }
            
            if($createConf) {
                User::getAuth()->setOption("home_accounts_toggle", $accountsToggle);
                User::getAuth()->save();
            }
            ?>
        </div>
        <div class="col-md-5">
            <h3>Dernières entrées</h3>
            <?php
            $database = new Database();
            $groups = Array();
            foreach(User::getAuth()->getGroups() as $group) {
                array_push($groups, $group->getId());
            }
            $request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "accounts WHERE aGroup IN (" . arrayToString($groups, "") . ") ORDER BY aName ASC");
            $request->execute();
            while($line = $request->fetch()) {
                $account = new Account();
                $account->loadFromRow($line);
                echo "<div data-account-id=\"{$account->getId()}\"" . (!$accountsToggle[$account->getId()] ? " style=\"display:none;\"" : "") . "><h4>{$account->aName}</h4><ul class=\"fa-ul last-entries\">";
                if($account->isLog()) {
                    $req = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "inputs INNER JOIN " . ENV_TABLES_PREFIX . "tags ON tId = iType WHERE iAccount = :account ORDER BY iDate DESC LIMIT 0, 5;");
                    $req->execute(Array("account" => $account->getId()));
                    while($row = $req->fetch()) {
                        echo "<li title=\"Dans <em>{$row['tName']}</em>\"><i class=\"fa fa-li fa-" . $row['tIcon'] . "\"></i>" . ($row['iNotes'] ? $row['iNotes'] : "<em>Sans description</em>") . "<span class=\"pull-right\">" . toEuros($row['iAmount'], true) . "</span></li>";
                    }
                    if(!$req->rowCount()) {
                        echo "<p><em>Ce compte ne contient aucune entrée.</em></p>";
                    }
                    echo "</ul>";
                } else {
                    $req = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "inputs INNER JOIN " . ENV_TABLES_PREFIX . "tags ON tId = iType INNER JOIN " . ENV_TABLES_PREFIX . "users ON uId = iUser WHERE iAccount = :account ORDER BY iDate DESC LIMIT 0, 5;");
                    $req->execute(Array("account" => $account->getId()));
                    while($row = $req->fetch()) {
                        echo "<li title=\"Dans <em>{$row['tName']}</em>, par {$row['uDisplayName']}\"><i class=\"fa fa-li fa-" . $row['tIcon'] . "\"></i>" . ($row['iNotes'] ? $row['iNotes'] : "<em>Sans description</em>") . "<span class=\"pull-right\">" . toEuros($row['iAmount'], true) . "</span></li>";
                    }
                    if(!$req->rowCount()) {
                        echo "<p><em>Ce compte ne contient aucune entrée.</em></p>";
                    }
                    echo "</ul>";
                }
                echo "</div>";
            }
            if(!$request->rowCount()) {
                echo "<p class=\"alert alert-info\">Ce panneau affiche les dernières entrées dans vos comptes. <a href=\"accounts/manage.html\">Cliquez ici pour créer un compte.</a></p>";
            }
            ?>
                <?php
                $addAccount = false;
                foreach(User::getAuth()->getGroups() as $group) {
                    if(User::getAuth()->isAdminOf($group->gName)) {
                        $addAccount = true;
                    }
                }
                if($addAccount) {
                    echo "<h3>Actions</h3>";
                    echo "<ul class=\"fa-ul\">";
                    echo "<li><a href=\"accounts/manage.html\"><i class=\"fa fa-cogs fa-li\"></i> Gérer les comptes</a></li>";
                    echo "<li class=\"install-action\"><a href=\"javascript:installApp();\"><i class=\"fa fa-download fa-li\"></i> Installer l'application</a></li>";
                    echo "</ul>";
                }
                ?>
        </div>
    </div>
    <script type="text/javascript">
    $(document).ready(function(){
        $(".last-entries li").tooltip({html: true, placement: "left"});
        $(".toggle").click(function(){
            $.post("ajax/accounts.php", {action: "set-home-display", account: $(this).data("id"), display: $(this).find("i").hasClass("fa-plus") ? 1 : 0});
            $(this).find("i").toggleClass("fa-plus fa-minus");
            $("[data-account-id=" + $(this).data("id") + "]").toggle();
        });
        $("body").keydown(function(e){
            if(e.ctrlKey) {
                $(".hotkey").show();
                var n = e.keyCode - 48;
                if(e.keyCode > 48 && e.keyCode < 58) {
                    $(".hotkey").each(function(){
                        if($(this).text() == "(" + n + ")") {
                            document.location.replace($(this).prev("a").attr("href"));
                        }
                    });
                }
            }
        });
        $("body").keyup(function(e){
            $(".hotkey").hide();
        })
    });
    </script>
    <?php
}
?>
