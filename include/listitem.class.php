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

    private $list = NULL;
    
    public function __construct($list) {
        parent::__construct("list_items", Array("iList", "iUser", "iContent", "iStatus", "iOrder", "iCreationDate", "iModificationDate"), "iId");
        $this->list = $list;
        $this->iList = $list->getId();
    }
    
    public function save() {
        $this->iModificationDate = time();
        $this->iUser = User::getAuth()->getId();
        parent::save();
    }
    
    public function create() {
        $this->iCreationDate = time();
        $this->iModificationDate = time();
        $this->iUser = User::getAuth()->getId();
        parent::create();
    }
    
    public function getList() {
        return $this->list;
    }
    
    public function setList($list) {
        $this->list = $list;
        $this->iList = $list->getId();
    }
    
}
