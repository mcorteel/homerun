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

class ItemsList extends DatabaseObject
{

    private $items = NULL;
    private $deletedItems = NULL;
    
    public function __construct() {
        parent::__construct("lists", Array("lTitle", "lIcon", "lGroup", "lCreationDate", "lModificationDate"));
    }
    
    public function loadFromId($id) {
        parent::loadFromId($id);
        $this->getItems();
        return true;
    }
    
    public function getItems($forceUpdate = false) {
        if($this->items === NULL || $forceUpdate) {
            $this->items = Array();
            $database = new Database();
            if($request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "list_items WHERE iList = :id;")) {
                if($request->execute(Array("id" => $this->getId()))) {
                    while($line = $request->fetch()) {
                        $o = new ListItem($this);
                        $o->loadFromRow($line);
                        $this->items[] = $o;
                    }
                } else {
                    error("Cannot execute request - " . print_r($request->errorInfo(), true));
                }
            } else {
                error("Cannot prepare request - " . print_r($database->errorInfo(), true));
            }
        }
        return $this->items;
    }
    
    public function addItem($item) {
        array_push($this->items, $item);
    }
    
    public function removeItem($item) {
        if($this->deletedItems === null) {
            $this->deletedItems = Array();
        }
        if($item->getId() === false) {
            warning("Trying to delete non-existing item");
            return false;
        }
        $succes = false;
        foreach($this->getItems() as $i => $o) {
            if($o->getId() == $item->getId()) {
                unset($this->items[$i]);
                $success = true;
            }
        }
        if($success) {
            array_push($this->deletedItems, $item);
        } else {
            warning("could not delete item {$item->getId()} as it does not belong to this list");
        }
    }
    
    public function save() {
        $this->lModificationDate = time();
        parent::save();
        foreach($this->items as $item) {
            if($item->getId() == false) {
                $item->create();
                debug("Creating item #{$item->getId()}");
            } else {
                $item->save();
                debug("Saving item #{$item->getId()} with value {$item->iContent}");
            }
        }
        foreach($this->deletedItems as $item) {
            debug("Removing item #{$item->getId()}");
            $item->delete();
        }
        $this->deletedItems = null;
    }
    
    public function create() {
        $this->lCreationDate = time();
        $this->lModificationDate = time();
        parent::create();
        foreach($this->items as $item) {
            $item->setList($this);
            $item->create();
        }
    }
    
    public function updateFromForm($method = "post", $prefix = "", $source = Array()) {
        $source = parent::updateFromForm($method, $prefix, $source);
        $_items = $source['items'];
        $existing = Array();
        $this->getItems();
        foreach($_items as $_item) {
            if(in_array('iId', array_keys($_item))) {
                //item already exists
                foreach($this->items as $index => $item) {
                    if($item->getId() == $_item['iId']) {
                        $i = $index;
                        array_push($existing, $item->getId());
                    }
                }
            } else {
                //new item
                $o = new ListItem($this);
                array_push($this->items, $o);
                $i = sizeof($this->items) - 1;
            }
            $this->items[$i]->updateFromForm("manual", "", $_item);
        }
        debug(sizeof($this->items) . " items");
        foreach($this->items as $item) {
            if($item->getId() !== false && !in_array($item->getId(), $existing)) {
                $this->removeItem($item);
            }
        }
    }
    
    public function getValues() {
        $array = parent::getValues();
        $array['items'] = Array();
        $items = Array();
        foreach($this->getItems() as $item) {
            $items[$item->iOrder] = $item->getValues();
        }
        ksort($items);
        foreach($items as $item) {
            array_push($array['items'], $item);
        }
        return $array;
    }
    
    public function hasItem($item) {
        foreach($this->getItems() as $i) {
            if($i->getId() === $item->getId()) {
                return true;
            }
        }
        return false;
    }
}
