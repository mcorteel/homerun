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

include_once("autoload.php");

class Database extends PDO {
    public function __construct() {
        try {
            parent::__construct("mysql:host=" . ENV_DB_ADDRESS . ";dbname=" . ENV_DB_NAME, ENV_DB_USER, ENV_DB_PASSWORD);
        } catch (Exception $e) {
            debug("Cannot open database: " . $e->getMessage());
            echo UI::error("Cannot open database. Please contact a system administrator.");
        }
        if(is_object($this)) {
            $this->query("SET NAMES utf8");
        }
    }
}
