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
$account = new Account();
$account->loadFromId($_POST['account']);

if(!User::getAuth()->hasGroup($account->getGroup()->gName))
{
    $data['status'] = 0;
    $data['error'] = "Vous n'avez pas accès à ce compte";
    send($data);
}

/**
 * Handle options
 */

if(isset($_POST['order'])) {
    $account->setStatsOrder($_POST['order']);
}
$accountOrder = $account->getStatsOrder();

if(isset($_POST['graphType']) && in_array($_POST['graphType'], Array('bar', 'line', 'area'))) {
    User::getAuth()->setOption("statsGraphType", $_POST['graphType']);
} elseif(!in_array(User::getAuth()->getOption("statsGraphType"), Array('bar', 'line', 'area'))) {
    User::getAuth()->setOption("statsGraphType", 'bar');
}
$data['graphType'] = User::getAuth()->getOption("statsGraphType");

User::getAuth()->save();

$fillColors = ["#4AA265", "#FF952B", "#825CF1"];
$strokeColors = ["#2E653E", "#CD7822", "#583FA4"];

/**
 * Handle request
 */

$month = (int)$_POST['sMonth'];
$year = (int)$_POST['sYear'];

$eMonth = $_POST['eMonth'];
$eYear = $_POST['eYear'];

$months = Array("", "Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Aoû", "Sep", "Oct", "Nov", "Déc");

$data['datasets'] = Array();
$data['labels'] = Array();

$tags = $account->getTags();
foreach($tags as $i => $tag) {
    debug("Adding dataset for {$tag->getId()}, with order {$accountOrder[$tag->getId()]}");
    $data['datasets'][$accountOrder[$tag->getId()]] = Array(
        "id" => $tag->getId(),
        "label" => $tag->tName,
        "backgroundColor" => $fillColors[$i],
        "borderColor" => $strokeColors[$i],
        "borderWidth" => 1,
        "display" => ($accountOrder[$tag->getId()] >= 0),
        "data" => Array()
    );
}

$database = new Database();

while($year < $eYear || $month <= $eMonth) {
    $request = $database->prepare("SELECT SUM(iAmount) AS sum, iType FROM " . ENV_TABLES_PREFIX . "inputs_{$account->aTable} WHERE MONTH(iDate) = :month AND YEAR(iDate) = :year GROUP BY iType;");
    $request->execute(Array("month" => $month, "year" => $year));
    $done = Array();
    while($line = $request->fetch()) {
        array_push($done, $line['iType']);
        array_push($data['datasets'][$accountOrder[$line['iType']]]['data'], round($line['sum'], 2));
    }
    if($request->rowCount() < sizeof($accountOrder)) {
        foreach($accountOrder as $id => $order) {
            if(!in_array($id, $done)) {
                array_push($data['datasets'][$order]['data'], 0);
            }
        }
    }
    array_push($data['labels'], "{$months[$month]} $year");
    //Increment month/year
    if($month == 12) {
        $year++;
        $month = 1;
    } else {
        $month++;
    }
}

send($data);
