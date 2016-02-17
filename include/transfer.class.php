<?php
/*****************************************************************************
 * Copyright 2013-2016 Maxime Corteel                                        *
 *                                                                           *
 * This file is part of Homerun                                              *
 *                                                                           *
 * Home Helper is free software: you can redistribute it and/or              *
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
 
class Transfer extends DatabaseObject
{

    private $sender = NULL;
    private $receiver = NULL;
    
    public function __construct() {
        parent::__construct("transfers", Array("tAccount", "tDate", "tNotes", "tSender", "tReceiver", "tAmount"));
    }
    
    public function getDate() {
        return mktime(12, 0, 0, substr($this->tDate, 5, 2), substr($this->tDate, 8, 2), substr($this->tDate, 0, 4));
    }
    
    public function setDate($timestamp) {
        $this->tDate = date("d-m-Y", $timestamp);
    }
    
    public function loadOtherFromRow($row, $stopThere = false) {
        if(isset($row['sender'])) {
            $this->sender = $row['sender'];
        }
        if(isset($row['receiver'])) {
            $this->receiver = $row['receiver'];
        }
    }
    
    public function getSender() {
        return $this->sender;
    }
    
    public function getReceiver() {
        return $this->receiver;
    }
    
}
