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

include_once("../include/ajax.php");

//Basic information
$data['action'] = $_POST['action'];
$linesPerPage = ENV_LINES_PER_PAGE;
if(isset($_POST['account'])) {
    $account = new Account();
    $account->loadFromId($_POST['account']);
    if(!User::getAuth()->hasGroup($account->getGroup()->gName)) {
        $data['status'] = 0;
        $data['error'] = "Vous n'avez pas accès à ce compte";
        send($data);
    }
    $data['account'] = $account->getId();
}

switch($_POST['action']) {
    case "create":
        //Determination of previous line ID
        $database = new Database();
        $request = $database->prepare("SELECT iId FROM " . ENV_TABLES_PREFIX . "inputs WHERE iAccount = :account AND iDate <= :date ORDER BY iDate DESC, iId DESC LIMIT 0, 1;");
        $request->execute(Array("account" => $account->getId(), "date" => $_POST['iDate']));
        if($line = $request->fetch()) {
            debug("Got previous ID #{$line['iId']}");
            $data['prevId'] = $line['iId'];
        }
        if(!$data['prevId']) {
            debug("No previous ID");
            $data['prevId'] = 0;
        }
        $request = $database->prepare("SELECT COUNT(*) AS c FROM " . ENV_TABLES_PREFIX . "inputs WHERE iAccount = :account AND iDate >= :date;");
        $request->execute(Array("account" => $account->getId(), "date" => $_POST['iDate']));
        $line = $request->fetch();
        $data['page'] = ceil($line['c'] / $linesPerPage);
        $request = $database->prepare("SELECT COUNT(*) AS c FROM " . ENV_TABLES_PREFIX . "inputs WHERE iAccount = :account;");
        $request->execute(Array("account" => $account->getId()));
        $line = $request->fetch();
        $data['pagesCount'] = ceil($line['c'] / $linesPerPage);
        //New input creation
        $input = new Input($account);
        $input->updateFromForm();
        if($account->isLog()) {
            $input->iUser = NULL;
        }
        $input->create();
        $array = $input->getValues();
        $array['iId'] = $input->getId();
        $array['iAmount'] = $input->getAmount();
        $array['iUser'] = $input->getUser()->uDisplayName;
        $array['iDisplayDate'] = timestampToCompact($input->getDate());
        $array['iIcon'] = $account->getIconOf($input);
        $data['input'] = $array;
        $data['status'] = 1;
        $data['message'] = "Ajouté";
        break;
    case "edit":
        $input = new Input($account);
        $input->loadFromId($_POST['iId']);
        $input->updateFromForm();
        $input->save();
        $array = $input->getValues();
        $array['iId'] = $input->getId();
        $array['iAmount'] = $input->getAmount();
        if($account->isLog()) {
            $array['iUser'] = "";
        } else {
            $array['iUser'] = $input->getUser()->uDisplayName;
        }
        $array['iDisplayDate'] = timestampToCompact($input->getDate());
        $array['iIcon'] = $account->getIconOf($input);
        $data['input'] = $array;
        $data['status'] = 1;
        $data['message'] = "Mis à jour";
        break;
    case "get":
        $input = new Input($account);
        $input->loadFromId($_POST['iId']);
        $array = $input->getValues();
        if($account->isLog()) {
            $array['iUser'] = "";
        } else {
            $array['iUser'] = $input->getUser()->uDisplayName;
        }
        $array['iUserId'] = $input->iUser;
        $array['iId'] = $input->getId();
        $array['iDisplayDate'] = timestampToCompact($input->getDate());
        $array['iIcon'] = $account->getIconOf($input);
        $data['input'] = $array;
        $data['status'] = 1;
        break;
    case "delete":
        $input = new Input($account);
        $input->loadFromId($_POST['iId']);
        $data['iId'] = $input->getId();
        $input->delete();
        $data['status'] = 1;
        $data['message'] = "Supprimé";
        break;
    case "get-list":
        $database = new Database();
        $firstOfPage = ($_POST['pageNumber'] - 1) * $linesPerPage;
        if($account->isLog()) {
            $request = $database->prepare("SELECT " . ENV_TABLES_PREFIX . "inputs.* FROM " . ENV_TABLES_PREFIX . "inputs WHERE iAccount = :account ORDER BY iDate DESC, iId DESC LIMIT $firstOfPage, $linesPerPage;");
        } else {
            $request = $database->prepare("SELECT " . ENV_TABLES_PREFIX . "inputs.*, uDisplayName FROM " . ENV_TABLES_PREFIX . "inputs INNER JOIN " . ENV_TABLES_PREFIX . "users ON iUser = uId WHERE iAccount = :account ORDER BY iDate DESC, iId DESC LIMIT $firstOfPage, $linesPerPage;");
        }
        $request->execute(Array("account" => $account->getId()));
        $list = Array();
        while($line = $request->fetch()) {
            $t = new Input($account);
            $t->loadFromRow($line);
            $line['iDisplayDate'] = timestampToCompact($t->getDate());
            if($account->isLog()) {
                $line['iUser'] = 0;
            } else {
                $line['iUser'] = $line['uDisplayName'];
            }
            $line['iAmount'] = toEuros($t->iAmount);
            $line['iIcon'] = $account->getIconOf($t);
            array_push($list, $line);
        }
        $request = $database->prepare("SELECT COUNT(*) AS total FROM " . ENV_TABLES_PREFIX . "inputs WHERE iAccount = :account;");
        $request->execute(Array("account" => $account->getId()));
        $line = $request->fetch();
        $data['pagesCount'] = ceil($line['total'] / $linesPerPage);
        $data['list'] = $list;
        $data['status'] = 1;
        $data['message'] = "Page {$_POST['pageNumber']}";
        break;
    case "search-list":
        //Parse search
        $searchString = $_POST['search'];
        $rCondition = Array();
        $rArray = Array();
        $a = Array();
        if(preg_match_all('/[a-z]+ ?(\:|>|<|=|~|<=|>=) ?([a-z0-9\/\.,]+|"[a-z 0-9]+")/', $searchString, $a)) {
            foreach($a[0] as $i => $j) {
                $b = Array();
                if(preg_match('/([a-z]+) ?(\:|>|<|=|~|<=|>=) ?([a-z0-9\/\.,]+|"[a-z 0-9]+")/', $j, $b)) {
                    $field = $b[1];
                    $relation = trim($b[2]);
                    $value = str_replace('"', "", $b[3]);
                    switch($field) {
                        case "prix":
                        case "montant":
                            $relations = Array("=" => "=", ":" => "=", ">" => ">", "<" => "<", "<=" => "<=", ">=" => ">=", "~" => "=");
                            if(in_array($relation, $relations)) {
                                $relation = $relations[$relation];
                            } else {
                                $relation = "=";
                            }
                            array_push($rCondition, "iAmount $relation :amount");
                            $rArray['amount'] = str_replace(",", ".", doubleval(str_replace(",", ".", $value)));
                            break;
                        case "date":
                            $d = Array();
                            if(preg_match("/([0-3][0-9])\/([0-1]?[0-9])\/([0-9]{4})/", $value, $d)) {
                                $d[1] = strlen($d[1]) == 1 ? "0" . $d[1] : $d[1];
                                $d[2] = strlen($d[2]) == 1 ? "0" . $d[2] : $d[2];
                                $d[3] = strlen($d[3]) == 1 ? "0" . $d[3] : $d[3];
                                $date = "{$d[3]}-{$d[2]}-{$d[1]}";
                            } elseif(preg_match("/([0-3][0-9])\/([0-1]?[0-9])/", $value, $d)) {
                                $d[1] = strlen($d[1]) == 1 ? "0" . $d[1] : $d[1];
                                $d[2] = strlen($d[2]) == 1 ? "0" . $d[2] : $d[2];
                                $date = "%{$d[2]}-{$d[1]}";
                            }
                            array_push($rCondition, "iDate LIKE :date");
                            $rArray['date'] = $date;
                            break;
                        default:
                            array_push($rCondition, "iNotes LIKE :notes");
                            $rArray['notes'] = "%$value%";
                            break;
                    }
                }
            }
        } else {
            array_push($rCondition, "iNotes LIKE :notes");
            $rArray['notes'] = "%$searchString%";
        }
        array_push($rCondition, "iAccount = :account");
        $rArray['account'] = $account->getId();
        $rCondition = arrayToString($rCondition, "", " AND ");
        debug($rCondition);
        debug(print_r($rArray, true));
        //Run search
        $database = new Database();
        if($account->isLog()) {
            $r = "SELECT " . ENV_TABLES_PREFIX . "inputs.* FROM " . ENV_TABLES_PREFIX . "inputs WHERE $rCondition ORDER BY iDate DESC, iId DESC LIMIT 0, 100;";
        } else {
            $r = "SELECT " . ENV_TABLES_PREFIX . "inputs.*, uDisplayName FROM " . ENV_TABLES_PREFIX . "inputs INNER JOIN " . ENV_TABLES_PREFIX . "users ON iUser = uId WHERE $rCondition ORDER BY iDate DESC, iId DESC LIMIT 0, 100;";
        }
        if(!$request = $database->prepare($r)) {
            debug("prepare error");
        }
        if(!$request->execute($rArray)) {
            debug("execute error: " . print_r($request->errorInfo(), true));
        }
        $list = Array();
        while($line = $request->fetch()) {
            $t = new Input($account);
            $t->loadFromRow($line);
            $line['iDisplayDate'] = timestampToCompact($t->getDate());
            $line['iUser'] = $line['uDisplayName'];
            $line['iAmount'] = toEuros($t->iAmount);
            $line['iIcon'] = $account->getIconOf($t);
            array_push($list, $line);
        }
        $request = $database->prepare("SELECT SUM(iAmount) AS total, COUNT(iAmount) AS n FROM " . ENV_TABLES_PREFIX . "inputs WHERE $rCondition;");
        $request->execute($rArray);
        if($line = $request->fetch()) {
            $data['total'] = toEuros($line['total']);
            $data['hits'] = $line['n'];
        }
        $data['list'] = $list;
        $data['status'] = 1;
        break;
    case "get-account":
        $array = $account->getValues();
        $array['aId'] = $account->getId();
        $data['account'] = $array;
        $tags = $account->getTags(true);
        $data['tags'] = Array();
        foreach($tags as $tag) {
            $array = $tag->getValues();
            $array['tId'] = $tag->getId();
            array_push($data['tags'], $array);
        }
        break;
    case "list-accounts":
        $accounts = User::getAuth()->getAccounts();
        $list = Array();
        foreach($accounts as $account) {
            $a = $account->getValues();
            $a['aId'] = $account->getId();
            $tags = $account->getTags(true);
            foreach($tags as $tag) {
                $b = $tag->getValues();
                $b['tId'] = $tag->getId();
                array_push($a['tags'], $b);
            }
            array_push($a, $list);
        }
        $data['list'] = $list;
        break;
    case "edit-account":
        foreach($_POST['tags'] as $t) {
            $tag = new DatabaseObject("tags", Array("tName", "tAccount", "tIcon"));
            if($t['tId'] != 0) {
                $tag->loadFromId($t['tId']);
                $tag->tName = $t['tName'];
                $tag->tIcon = $t['tIcon'];
                $tag->save();
            } else {
                $tag->tName = $t['tName'];
                $tag->tIcon = $t['tIcon'];
                $tag->tAccount = $account->getId();
                $tag->create();
            }
        }
        $account->aName = $_POST['aName'];
        $account->aIcon = $_POST['aIcon'];
        if($account->isLog()) {
            $account->aLimit = (int)$_POST['aLimit'];
        }
        $account->save();
        $data['account'] = $account->getValues();
        $data['account']['aId'] = $account->getId();
        $data['status'] = 1;
        break;
    case "set-home-display":
        $a = is_array(User::getAuth()->getOption("home_accounts_toggle")) ? User::getAuth()->getOption("home_accounts_toggle") : Array();
        $a[$account->getId()] = intval($_POST['display']);
        User::getAuth()->setOption("home_accounts_toggle", $a);
        User::getAuth()->save();
        break;
    case "create-account":
        $account = new Account();
        $account->aGroup = $_POST['aGroup'];
        $account->aName = $_POST['aName'];
        $account->aLog = $_POST['aLog'];
        $account->aLimit = $_POST['aLimit'];
        $account->aIcon = $_POST['aIcon'];
        $account->create();
        User::getAuth()->getAccounts(true);
        foreach($_POST['tags'] as $t) {
            $tag = new DatabaseObject("tags", Array("tName", "tAccount", "tIcon"));
            $tag->tName = $t['tName'];
            $tag->tIcon = $t['tIcon'];
            $tag->tAccount = $account->getId();
            $tag->create();
        }
        $data['account'] = $account->getValues();
        $data['account']['aId'] = $account->getId();
        $data['status'] = 1;
        break;
    case "delete-account":
        $data['account'] = $account->getValues();
        $data['account']['aId'] = $account->getId();
        $account->delete();
        $data['status'] = 1;
        break;
    case "get-transfers":
        $database = new Database();
        $request = $database->prepare("SELECT transfers.*, senders.uDisplayName AS sender, receivers.uDisplayName AS receiver FROM (" . ENV_TABLES_PREFIX . "transfers AS transfers INNER JOIN " . ENV_TABLES_PREFIX . "users AS senders ON tSender = senders.uId) INNER JOIN " . ENV_TABLES_PREFIX . "users AS receivers ON tReceiver = receivers.uId WHERE tAccount = :account ORDER BY tDate DESC, tId DESC;");
        $request->execute(Array("account" => $account->getId()));
        $data['transfers'] = Array();
        while($line = $request->fetch()) {
            $transfer = new Transfer();
            $transfer->loadFromRow($line);
            $array = $transfer->getValues();
            $array['sender'] = $transfer->getSender();
            $array['receiver'] = $transfer->getReceiver();
            $array['tAmount'] = toEuros($transfer->tAmount);
            $array['tDate'] = timestampToCompact($transfer->getDate());
            $array['tId'] = $transfer->getId();
            array_push($data['transfers'], $array);
        }
        $data['users'] = Array();
        $data['defaultSender'] = User::getAuth()->getId();
        foreach($account->getGroup()->getMembers() as $user) {
            $data['users'][$user->getId()] = $user->uDisplayName;
        }
        break;
    case "create-transfer":
        $transfer = new Transfer();
        $transfer->updateFromForm();
        debug($_POST['tDate']);
        $transfer->tAccount = $account->getId();
        $transfer->create();
        $data['transfer'] = $transfer->getValues();
        $sender = new User();
        $sender->loadFromId($transfer->tSender);
        $receiver = new User();
        $receiver->loadFromId($transfer->tReceiver);
        $data['transfer']['sender'] = $sender->uDisplayName;
        $data['transfer']['receiver'] = $receiver->uDisplayName;
        $data['transfer']['tId'] = $transfer->getId();
        $data['transfer']['tDate'] = timestampToCompact($transfer->getDate());
        $data['transfer']['tAmount'] = toEuros($transfer->tAmount);
        $data['status'] = 1;
        $data['message'] = "Le transfert a bien été ajouté.";
        break;
    case "delete-transfer":
        $transfer = new Transfer();
        $transfer->loadFromId($_POST['tId']);
        $data['tId'] = $transfer->getId();
        $transfer->delete();
        $data['status'] = 1;
        $data['message'] = "Le transfert a bien été supprimé.";
        break;
    default:
        $data['status'] = 0;
        $data['error'] = "invalid action";
        break;
}

send($data);
