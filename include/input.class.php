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

class Input extends DatabaseObject
{

    private $user = NULL;
    private $receiver = NULL;
    
    public function __construct($table) {
        parent::__construct("inputs_$table", Array("iType", "iUser", "iAmount", "iDate", "iNotes"));
    }
    
    public function loadOtherFromRow($row) {
        if(isset($row["uId"])) {
            $this->user = new User();
            $this->user->loadFromRow($row);
        }
    }
    
    public function getUser() {
        if($this->user === NULL) {
            $this->user = new User();
            $this->user->loadFromId($this->iUser);
        }
        return $this->user;
    }
    
    public function getDate() {
        return mktime(12, 0, 0, substr($this->iDate, 5, 2), substr($this->iDate, 8, 2), substr($this->iDate, 0, 4));
    }
    
    public function setDate($timestamp) {
        $this->iDate = date("d-m-Y", $timestamp);
    }
    
    public function getAmount() {
        return toEuros($this->iAmount);
    }
    
}
