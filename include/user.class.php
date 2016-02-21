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

class User extends DatabaseObject
{

    private $groups = NULL;
    private $admin;
    private $options = NULL;
    private $accounts = NULL;
    private $lists = NULL;
    
    public function __construct() {
        parent::__construct("users", Array("uDisplayName", "uEmail", "uLogin", "uPassword", "uLastLogin", "uOptions", "uCreationDate", "uModificationDate"));
    }
    
    public static function getHash() {
        $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        $salt = base64_encode($salt);
        $salt = str_replace('+', '.', $salt);
        $hash = crypt('098f6bcd4621d373cade4e832627b4f6', '$2y$10$'.$salt.'$');
        debug($hash);
        return $hash;
    }
    
    /**
     * Groups management functions
     **/
    
    public function getGroups($forceUpdate = false) {
        if($this->groups === NULL || $forceUpdate) {
            $this->groups = Array();
            $this->admin = Array();
            $database = new Database();
            if($request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "groups AS groups INNER JOIN " . ENV_TABLES_PREFIX . "members AS members ON members.mGroupId = groups.gId WHERE members.mUserId = :id ORDER BY gName;")) {
                if($request->execute(Array("id" => $this->getId()))) {
                    while($line = $request->fetch()) {
                        if($line['mAdmin']) {
                            $this->admin[$line['gId']] = true;
                        } else {
                            $this->admin[$line['gId']] = false;
                        }
                        $group = new Group();
                        $group->loadFromRow($line);
                        $this->groups[] = $group;
                    }
                } else {
                    error("cannot execute request - " . print_r($request->errorInfo(), true));
                }
            } else {
                error("cannot prepare request - " . print_r($database->errorInfo(), true));
            }
        }
        return $this->groups;
    }
    
    public function addGroup($groupName) {
        $userGroups = $this->getGroups();
        foreach($userGroups as $group) {
            if($groupName == $group->gName) {
                return true;
            }
        }
        $database = new Database();
        if($request = $database->prepare("INSERT INTO " . ENV_TABLES_PREFIX . "members(mUserId, mGroupId, mAdmin) VALUES (:userId, (SELECT gId FROM " . ENV_TABLES_PREFIX . "groups WHERE gName = :groupName), 0);")) {
            if($request->execute(Array("userId" => $this->getId(), "groupName" => $groupName))) {
                $this->getGroups(true);
                return true;
            } else {
                error("cannot execute request - " . print_r($request->errorInfo(), true));
            }
        } else {
            error("cannot prepare request - " . print_r($database->errorInfo(), true));
        }
        return false;
    }
    
    public function setGroups($groupsArray) {
        $userGroups = $this->getGroups(true);
        foreach($groupsArray as $groupName) {
            if(!$this->hasGroup($groupName)) {
                $this->addGroup($groupName);
            }
        }
        foreach($userGroups as $group) {
            if(!in_array($group->gName, $groupsArray)) {
                $this->removeGroup($group->getId());
            }
        }
        $this->getGroups(true);
    }
    
    //WARNING: This function takes a group ID as argument, and *not* a group name
    public function removeGroup($groupId) {
        $database = new Database();
        if($request = $database->prepare("DELETE FROM " . ENV_TABLES_PREFIX . "members WHERE mUserId = :userId AND mGroupId = :groupId;")) {
            if($request->execute(Array("userId" => $this->getId(), "groupId" => $groupId))) {
                return true;
            } else {
                error("cannot execute request - " . print_r($request->errorInfo(), true));
            }
        } else {
            error("cannot prepare request - " . print_r($database->errorInfo(), true));
        }
        return false;
    }
    
    public function hasGroup($groupName) {
        $groups = $this->getGroups();
        foreach($groups as $group) {
            if($group->gName == $groupName) {
                return true;
            }
        }
        return false;
    }
    
    public function isAdminOf($groupName) {
        $groups = $this->getGroups();
        foreach($groups as $group) {
            if($group->gName == $groupName) {
                if($this->admin[$group->getId()]) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * User options functions
     **/
    
    public function getOption($key) {
        if($this->options == NULL && $this->uOptions != "") {
            $this->options = json_decode($this->uOptions, true);
        }
        if($this->options && in_array($key, array_keys($this->options))) {
            return $this->options[$key];
        } else {
            return NULL;
        }
    }
    
    public function setOption($key, $value) {
        if($this->options == NULL && $this->uOptions != "") {
            $this->options = json_decode($this->uOptions, true);
        }
        if(!$this->options) {
            $this->options = Array();
        }
        $this->options[$key] = $value;
        $this->uOptions = json_encode($this->options);
    }
    
    public function removeOption($key) {
        if($this->options == NULL && $this->uOptions != "") {
            $this->options = json_decode($this->uOptions, true);
        }
        if(in_array($key, array_keys($this->options))) {
            unset($this->options[$key]);
        }
        $this->uOptions = json_encode($this->options);
    }
    
    /**
     * Generic functions
     **/
    
    public function toString() {
        return $this->uDisplayName;
    }
    
    public function getLink() {
        return "<a target=\"_blank\" href=\"user/view/{$this->getId()}\">{$this->uDisplayName}</a>";
    }
    
    /**
     * Accounts functions
     **/
    
    public function getAccounts($forceUpdate = false) {
        if($this->accounts == NULL || $forceUpdate) {
            $this->accounts = Array();
            $database = new Database();
            $groups = Array();
            foreach($this->getGroups() as $group) {
                array_push($groups, $group->getId());
            }
            $request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "accounts WHERE aGroup IN (" . arrayToString($groups, "") . ") ORDER BY aName ASC");
            $request->execute();
            while($line = $request->fetch()) {
                $account = new Account();
                $account->loadFromRow($line);
                array_push($this->accounts, $account);
            }
        }
        return $this->accounts;
    }
    
    public function getLists($forceUpdate = false) {
        if($this->lists == NULL || $forceUpdate) {
            $this->lists = Array();
            $database = new Database();
            $groups = Array();
            foreach($this->getGroups() as $group) {
                array_push($groups, $group->getId());
            }
            $request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "lists WHERE lGroup IN (" . arrayToString($groups, "") . ") ORDER BY lModificationDate DESC;");
            $request->execute();
            while($line = $request->fetch()) {
                $list = new ItemsList();
                $list->loadFromRow($line);
                array_push($this->lists, $list);
            }
        }
        return $this->lists;
    }
    
    /**
     * Static functions
     **/
    
    static function getAuth() {
        if(isset($_SESSION['authUser']) && is_object($_SESSION['authUser'])) {
            return $_SESSION['authUser'];
        }
        if(isset($_COOKIE['login']) && isset($_COOKIE['password'])) {
            $authUser = new User();
            $database = new Database();
            if($request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "users WHERE uLogin = :login;")) {
                if($request->execute(Array("login" => $_COOKIE['login']))) {
                    if($line = $request->fetch()) {
                        if($line['uPassword'] == $_COOKIE['password']) {
                            $authUser->loadFromRow($line);
                            $authUser->uLastLogin = time();
                            $authUser->save();
                            $_SESSION['authUser'] = $authUser;
                            return $authUser;
                        } else {
                            warning("cannot authenticate user stored in cookie: wrong password");
                        }
                    } else {
                        warning("cannot authenticate user stored in cookie: user does not exist");
                    }
                } else {
                    error("cannot execute request - " . print_r($request->errorInfo(), true));
                }
            } else {
                error("cannot prepare request - " . print_r($database->errorInfo(), true));
            }
        }
        return false;
    }
    
}
