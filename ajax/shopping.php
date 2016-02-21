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

if(in_array($_POST['action'], Array("create-list", "page-init"))) {
    switch($_POST['action']) {
        case "create-list":
            $list = new ItemsList();
            $list->updateFromForm("manual", "", $_POST['list']);
            $list->create();
            $data['list'] = $list->getValues();
            $data['status'] = 1;
            $data['message'] = "Ajouté";
            break;
        case "page-init":
            $lists = Array();
            $l = User::getAuth()->getLists(true);
            foreach($l as $list) {
                array_push($lists, $list->getValues());
            }
            $groups = Array();
            $g = User::getAuth()->getGroups();
            foreach($g as $group) {
                array_push($groups, $group->getValues());
            }
            $data['lists'] = $lists;
            $data['groups'] = $groups;
            $data['status'] = 1;
            $data['message'] = "Page chargée";
            break;
    }
} else {
    $list = new ItemsList();
    if(!$list->loadFromId($_POST['lId'])) {
        //TODO: check rights
        $data['status'] = 0;
        $data['error'] = "This list does not exist";
        send($data);
    }
    
    switch($_POST['action']) {
        case "edit-list":
            $list->updateFromForm("manual", "", $_POST['list']);
            $list->save();
            $data['list'] = $list->getValues();
            $data['status'] = 1;
            $data['message'] = "Liste mise à jour";
            break;
        case "get-list":
            $data['list'] = $list->getValues();
            $data['status'] = 1;
            break;
        case "delete-list":
            $list->delete();
            $data['status'] = 1;
            $data['message'] = "Liste supprimée";
            break;
        default:
            $data['status'] = 0;
            $data['error'] = "invalid action";
            break;
    }
}

send($data);
