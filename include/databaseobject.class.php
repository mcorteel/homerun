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

class DatabaseObject {
    private $table;
    private $id = false;
    private $values = Array();
    protected $_values = Array();
    private $idField;
    protected $type;
    
    public function __construct($table, $fields) {
        $this->table = ENV_TABLES_PREFIX . $table;
        $this->type = get_class($this);
        $this->idField = substr($table, 0, 1) . "Id";
        foreach($fields as $field => $value) {
            if(is_int($field)) {
                $this->values[$value] = NULL;
            } else {
                $this->values[$field] = $value;
            }
        }
        $this->_values = $this->values;
    }
    
    public function loadFromId($id) {
        $database = new Database();
        $requestText = "SELECT " . arrayToString(array_keys($this->values), "`") . " FROM " . $this->table . " WHERE {$this->idField}=:id;";
        if($request = $database->prepare($requestText)) {
            if($request->execute(Array("id" => $id))) {
                if($line = $request->fetch()) {
                    $fields = array_keys($this->values);
                    foreach($fields as $field) {
                        if(isset($line[$field])) {
                            $this->values[$field] = $line[$field];
                        } else {
                            warning("field '$field' doesn't exist in the database");
                        }
                    }
                    $this->id = $id;
                    $this->_values = $this->values;
                    return true;
                } else {
                    warning("{$this->type} object #$id doesn't exist");
                    return false;
                }
            } else {
                error("cannot execute request $requestText - " . print_r($request->errorInfo(), true));
                return false;
            }
        } else {
            error("cannot prepare request $requestText - " . print_r($database->errorInfo(), true));
            return false;
        }
    }
    
    public function loadFromUniqueField($field, $value) {
        if(!in_array($field, array_keys($this->values))) {
            error("field $field does not exist for this object");
            return false;
        }
        $database = new Database();
        $requestText = "SELECT * FROM " . $this->table . " WHERE $field = :value;";
        if($request = $database->prepare($requestText)) {
            if($request->execute(Array("value" => $value))) {
                if($request->rowCount() != 1) {
                    error("cannot determine a unique object with this key/value pair ($field / $value)");
                    return false;
                }
                if($line = $request->fetch()) {
                    $fields = array_keys($this->values);
                    foreach($fields as $field) {
                        if(isset($line[$field])) {
                            $this->values[$field] = $line[$field];
                        } else {
                            warning("field '$field' doesn't exist in the database");
                        }
                    }
                    $this->id = $line[$this->idField];
                    $this->_values = $this->values;
                    return true;
                }
            } else {
                error("cannot execute request $requestText - " . print_r($request->errorInfo(), true));
            }
        } else {
            error("cannot prepare request $requestText - " . print_r($database->errorInfo(), true));
        }
        return false;
    }
    
    public function loadFromRow($row, $prefix = "", $stopThere = false) {
        if(isset($row[$prefix.$this->idField])) {
            $this->id = $row[$prefix.$this->idField];
        }
        foreach($this->values as $field => $value) {
            if(isset($row[$prefix.$field])) {
                $this->values[$field] = $row[$prefix.$field];
            }
        }
        $this->_values = $this->values;
        if(!$stopThere && method_exists($this, "loadOtherFromRow")) {
            $this->loadOtherFromRow($row);
        }
        return true;
    }
    
    /**
     * Copy an object from another one of the same type
     **/
    
    public function loadFromObject($object) {
        foreach($this->values as $field => $value) {
            $this->set($field, $object->get($field));
        }
        return true;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function get($field) {
        if(in_array($field, array_keys($this->values))) {
            if(preg_match("#(Modification|Creation)Date$#", $field)) {
                return SQLTT($this->values[$field]);
            } else {
                return $this->values[$field];
            }
        }
        warning("field '$field' doesn't exist");
        return NULL;
    }
    
    public function __get($field) {
        return $this->get($field);
    }
    
    public function set($field, $value) {
        if(in_array($field, array_keys($this->values))) {
            if(preg_match("#(Modification|Creation)Date$#", $field)) {
                $this->values[$field] = TTSQL($value);
            } else {
                $this->values[$field] = $value;
            }
            return true;
        }
        error("cannot set field \"$field\" for $this->type object because it doesn't exist");
        return false;
    }
    
    public function __set($field, $value) {
        return $this->set($field, $value);
    }
    
    public function create() {
        if($this->id !== false) {
            error("cannot create {$this->type} object because it already exists");
            return false;
        }
        $fields = Array();
        $tags = Array();
        foreach($this->values as $field => $value) {
            if($this->values[$field] !== NULL) {
                $fields[$field] = $value;
                $tags[] = ":$field";
            }
        }
        $database = new Database();
        $requestText = "INSERT INTO " . $this->table . "(" . arrayToString(array_keys($fields), "") . ") VALUES (" . arrayToString($tags, "") . ")";
        if($request = $database->prepare($requestText)) {
            if($request->execute($fields)) {
                $this->id = $database->lastInsertId();
                $this->_values = $this->values;
                return true;
            } else {
                error("cannot execute request $requestText - " . print_r($request->errorInfo(), true));
            }
        } else {
            error("cannot prepare request $requestText - " . print_r($database->errorInfo(), true));
        }
        return false;
    }
    
    public function save() {
        if($this->id === false) {
            error("cannot save {$this->type} object because it doesn't exist");
            return false;
        }
        if(method_exists($this, "updateLinkedContent")) {
            $this->updateLinkedContent();
        }
        if($this->_values == $this->values) {
            return true;
        }
        $up = Array();
        $upTags = Array();
        foreach($this->values as $field => $value) {
            if($this->values[$field] != $this->_values[$field]) {
                $up[$field] = $value;
                $upTags[] = "$field = :$field";
            }
        }
        $up["id"] = $this->id;
        $database = new Database();
        $requestText = "UPDATE " . $this->table . " SET " . arrayToString($upTags, "") . " WHERE {$this->idField}=:id;";
        if($request = $database->prepare($requestText)) {
            if($request->execute($up)) {
                $this->_values = $this->values;
                return true;
            } else {
                error("cannot execute request $requestText - " . print_r($request->errorInfo(), true));
            }
        } else {
            error("cannot prepare request $requestText - " . print_r($database->errorInfo(), true));
        }
        return false;
    }
    
    public function delete() {
        if($this->id === false) {
            error("cannot delete {$this->type} object because it doesn't exist");
            return false;
        }
        if(method_exists($this, "deleteLinkedContent")) {
            $this->deleteLinkedContent();
        }
        $database = new Database();
        $requestText = "DELETE FROM " . $this->table . " WHERE {$this->idField}=:id;";
        if($request = $database->prepare($requestText)) {
            if($request->execute(Array("id" => $this->id))) {
                $this->id = false;
                return true;
            } else {
                error("cannot execute request $requestText - " . print_r($request->errorInfo(), true));
            }
        } else {
            error("cannot prepare request $requestText - " . print_r($database->errorInfo(), true));
        }
        return false;
    }
    
    public function hasChanged($field) {
        return ($this->values[$field] != $this->_values[$field]);
    }
    
    public function serialize() {
        $array = $this->values;
        $array[$this->idField] = $this->getId();
        return json_encode($array);
    }
    
    public function getValues() {
        return $this->values;
    }
    
    public function toArray() {
        $array = $this->values;
        $array[$this->idField] = $this->getId();
        return $array;
    }
    
    public function updateFromForm($method = "post", $prefix = "", $source = Array()) {
        switch($method) {
            case "post":
                $source = $_POST;
                break;
            case "manual":
                break;
            default:
                $source = $_GET;
                break;
        }
        foreach($this->values as $field => $value) {
            if(isset($source["$prefix$field"])) {
                if(is_array($source["$prefix$field"])) {
                    //Serialize arrays
                    $this->set($field, json_encode($source["$prefix$field"]));
                } else {
                    $this->set($field, $source["$prefix$field"]);
                }
            }
        }
    }
}
