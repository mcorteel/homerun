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
 
class Account extends DatabaseObject
{

    private $group = NULL;
    private $tags = NULL;
    
    public function __construct() {
        parent::__construct("accounts", Array("aName", "aIcon", "aGroup", "aLog", "aLimit"));
    }
    
    public function loadOtherFromRow($row) {
        if(isset($row["gId"])) {
            $this->group = new Group();
            $this->group->loadFromRow($row);
        }
    }
    
    public function isLog() {
        return (bool)$this->aLog;
    }
    
    public function getGroup() {
        if($this->group === NULL) {
            $this->group = new Group();
            $this->group->loadFromId($this->aGroup);
        }
        return $this->group;
    }
    
    public function getTags() {
        if($this->tags == NULL) {
            $tags = Array();
            $database = new Database();
            $request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "tags WHERE tAccount = :account");
            $request->execute(Array("account" => $this->getId()));
            while($line = $request->fetch()) {
                $tag = new DatabaseObject("tags", Array("tName", "tAccount", "tIcon"));
                $tag->loadFromRow($line);
                array_push($tags, $tag);
            }
            $this->tags = $tags;
        }
        return $this->tags;
    }
    
    public function getIconOf($input) {
        foreach($this->getTags() as $tag) {
            if($tag->getId() == $input->iType) {
                return $tag->tIcon;
            }
        }
    }
    
    public function setStatsOrder($order) {
        //Delete empty indexes
        foreach($order as $i => $v) {
            if($v == "") {
                unset($order[$i]);
            }
        }
        $statsOrder = User::getAuth()->getOption("statsOrder");
        if(!is_array($statsOrder)) {
            $statsOrder = Array();
        }
        $statsOrder[$this->getId()] = $order;
        User::getAuth()->setOption("statsOrder", $statsOrder);
    }
    
    public function getStatsOrder() {
        $statsOrder = User::getAuth()->getOption("statsOrder");
        $modified = false;
        if(!is_array($statsOrder)) {
            $statsOrder = Array();
        }
        if(!in_array($this->getId(), array_keys($statsOrder))) {
            $statsOrder[$this->getId()] = Array();
        }
        foreach($this->getTags() as $tag) {
            if(!in_array($tag->getId(), array_keys($statsOrder[$this->getId()]))) {
                $statsOrder[$this->getId()][$tag->getId()] = max(max(array_values($statsOrder[$this->getId()])), 0) + 1;
            }
        }
        return $statsOrder[$this->getId()];
    }
    
}

