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
 
class Group extends DatabaseObject
{

    private $members = NULL;
    private $admins = NULL;
    private $accounts = NULL;
    
    public function __construct() {
        parent::__construct("groups", Array("gName", "gCreationDate", "gSystem", "gModificationDate"));
    }
    
    public function getMembers($forceUpdate = false) {
        if($this->members === NULL || $forceUpdate) {
            $this->members = Array();
            $this->admins = Array();
            $database = new Database();
            if($request = $database->prepare("SELECT mAdmin, users.*, mUserId AS uId FROM " . ENV_TABLES_PREFIX . "users AS users INNER JOIN " . ENV_TABLES_PREFIX . "members ON mUserId = uId WHERE mGroupId = :id ORDER BY uDisplayName;")) {
                if($request->execute(Array("id" => $this->getId()))) {
                    while($line = $request->fetch()) {
                        $u = new User();
                        $u->loadFromRow($line);
                        $this->members[] = $u;
                        if($line['mAdmin']) {
                            $this->admins[] = $u;
                        }
                    }
                } else {
                    error("cannot execute request - " . print_r($request->errorInfo(), true));
                }
            } else {
                error("cannot prepare request - " . print_r($database->errorInfo(), true));
            }
        }
        return $this->members;
    }
    
    public function deleteLinkedContent() {
        $database = new Database();
        $request = $database->prepare("DELETE FROM " . ENV_TABLES_PREFIX . "members WHERE mGroupId = :id;");
        $request->execute(Array("id" => $this->getId()));
    }
    
    public function getAdmins() {
        if($this->admins === NULL) {
            $this->getMembers();
        }
        return $this->admins;
    }
    
    public function isAdmin($user) {
        foreach($this->getAdmins() as $admin) {
            if($user->getId() == $admin->getId()) {
                return true;
            }
        }
        return false;
    }
    
    public function toString() {
        return $this->gName;
    }
}
