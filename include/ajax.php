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

header("Content-type: text/json");
error_reporting(E_ERROR | E_PARSE);
ini_set("display_errors", 1);

chdir("..");
include_once("include/utilities.php");
include_once("include/environment.php");
include_once("include/autoload.php");


//This is used to change the prefix in debug to (ajax) instead of (php)
$ENV_AJAX = true;


/**
 * When called, this function sends data back in json format to the client
 **/
function send($data) {
    global $_DEBUG;
    $data['debug'] = $_DEBUG;
    exit(json_encode($data));
}

//The data that will be sent back
$data = Array();
?>
