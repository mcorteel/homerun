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

class ListItem extends DatabaseObject
{

    private $items = NULL;
    
    public function __construct() {
        parent::__construct("lists", Array("lName", "lIcon", "lGroup", "lCreationDate", "lModificationDate"));
    }
    
    public function getItems($forceUpdate = false) {
        if($this->items === NULL || $forceUpdate) {
            $this->items = Array();
            $database = new Database();
            if($request = $database->prepare("SELECT * FROM " . ENV_TABLES_PREFIX . "list_items WHERE iList = :id;")) {
                if($request->execute(Array("id" => $this->getId()))) {
                    while($line = $request->fetch()) {
                        $o = new ListItem();
                        $o->loadFromRow($line);
                        $this->items[] = $o;
                    }
                } else {
                    error("cannot execute request - " . print_r($request->errorInfo(), true));
                }
            } else {
                error("cannot prepare request - " . print_r($database->errorInfo(), true));
            }
        }
        return $this->items;
    }
    
    public function addItem($item) {
        array_push($this->items, $item);
    }
    
    public function removeItem($item) {
        $succes = false
        foreach($this->getItems() as $i => $o) {
            if($o->getId() == $item->getId()) {
                unset($this->items[$i]);
                $success = true;
            }
        }
        if($success) {
            $item->delete();
        } else {
            warning("could not delete item {$item->getId()} as it does not belong to this list");
        }
    }
    
    public function updateFromForm($method = "post", $prefix = "", $source = Array()) {
        $source = parent::updateFromForm();
        if(in_array('items', array_keys($source))) {
            $items = $source['items'];
            $existing = Array();
            foreach($items as $item) {
                $o = new Item($this);
                $o->updateFromForm("manual", "", $item);
                if($o->getId() === false) {
                    $o->create();
                    array_push($this->items, $o);
                } else {
                    if($this->hasItem($o)) {
                        $o->save();
                    } else {
                        error("Trying to edit external item");
                    }
                }
                array_push($existing, $o->getId());
            }
            foreach($this->getItems() as $item) {
                if(!in_array($item->getId(), $existing)) {
                    $this->removeItem($item);
                }
            }
        }
    }
    
    public function getValues() {
        $array = parent::getValues();
        $array['items'] = Array();
        foreach($this->getItems() as $item) {
            $array['items'][$item->iOrder] = $item->getValues();
        }
        return $array;
    }
}
