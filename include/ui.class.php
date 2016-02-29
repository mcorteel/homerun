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
 
class UI
{
    
    public static function breadcrumbs($path) {
        $ret = "<ul class=\"breadcrumbs\">";
        $path = substr($path, 1);
        $pathArray = Array();
        $ret .= "<li class=\"root\"><a href=\"navigation.html\" title=\"Back to programme selection\"><i class=\"fa fa-plane\"></i></a></li>";
        $k = 0;
        while(strpos($path, "/")) {
            array_push($pathArray, substr($path, 0, strpos($path, "/")));
            $ret .=  "<li class=\"item\"><a href=\"navigation";
            foreach($pathArray as $i) {
                $ret .=  "/" . StructuralItem::escape($i);
            }
            $ret .=  "\" class=\"title\">" . str_replace("\\", "/", substr($path, 0, strpos($path, "/"))) . "</a><span class=\"selector\"><i class=\"fa fa-caret-right\"></i></span></li>";
            $path = substr($path, strpos($path, "/") + 1);
        }
        if($path == "") {
            $ret .= " Programme Selection";
        } else {
            array_push($pathArray, $path);
            $ret .= "<li class=\"item\"><span class=\"title\">" . str_replace("\\", "/", $path) . "</span></li>";
        }
        $ret .= "<a href=\"diagram.html\" class=\"action pull-right\" style=\"margin-right:1ex;\" title=\"Go to diagram view\"><i class=\"fa fa-sitemap\"></i></a>";
        $ret .= "</ul>";
        return $ret;
    }
    
    public static function p($text, $classes = "") {
        if(is_array($classes)) {
            return "<p class=\"" . str_replace(",", "", arrayToString($classes, "")) . "\">$text</p>";
        } else {
            return "<p class=\"$classes\">$text</p>";
        }
    }
    
    public static function li($text, $classes = "") {
        if(is_array($classes)) {
            return "<li class=\"" . str_replace(",", "", arrayToString($classes, "")) . "\">$text</li>";
        } else {
            return "<li class=\"$classes\">$text</li>";
        }
    }
    
    public static function alert($text, $level = "warning", $dismissable = false) {
        if($dismissable) {
            $addon = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
        } else {
            $addon = "";
        }
        return "<p class=\"alert alert-$level\">$text$addon</p>";
    }
    
    public static function error($text, $dismissable = false) {
        return UI::alert($text, "danger", $dismissable);
    }
    
    public static function warning($text, $dismissable = false) {
        return UI::alert($text, "warning", $dismissable);
    }
    
    public static function info($text, $dismissable = false) {
        return UI::alert($text, "info", $dismissable);
    }
    
    public static function select($name, $values, $defaultValue = NULL, $classes = "", $forceAssoc = false) {
        if(is_array($classes)) {
            $classes = str_replace(",", "", arrayToString($classes, ""));
        }
        $ret = "<select class=\"form-control $classes\" id=\"$name\" name=\"$name\">\n";
        
        $defaultVal = "";
        $options = "";
        
        foreach($values as $value => $text) {
            if(is_array($text)) {
                $text = array_values($text);
                $text = $text[0];
            }
            if(!(bool)count(array_filter(array_keys($values), 'is_string')) && !$forceAssoc) {
                //if $values is not an associative array
                $value = $text;
            }
            if($value == $defaultValue) {
                $defaultVal = "<option value=\"$value\">" . str_replace("\\", "/", $text) . "</option>\n";
            } else {
                $options .= "<option value=\"$value\">" . str_replace("\\", "/", $text) . "</option>\n";
            }
        }
        return "$ret$defaultVal$options</select>";
    }
    
    public static function pagination($pageNumber, $totalNumberOfItems, $itemsPerPage, $url) {
        if($totalNumberOfItems > $itemsPerPage) {
            $ret = "<ul class=\"pagination\">";
            $currentPage = 0;
            if($pageNumber == 1) {
                $ret .= "<li class=\"disabled\"><a href=\"" . str_replace("%", $pageNumber, $url) . "\">&laquo;</a></li>";
            } else {
                $ret .= "<li title=\"Previous Page\"><a href=\"" . str_replace("%", ($pageNumber - 1), $url) . "\" data-page=\"" . ($pageNumber - 1) . "\">&laquo;</a></li>";
            }
            
            $totalPages = ceil($totalNumberOfItems / $itemsPerPage);
            if($pageNumber - 2 > 1) {
                $ret .= "<li><span class=\"ellipsis\"><i class=\"fa fa-ellipsis-h\"></i></span></li>";
            }
            for($currentPage = $pageNumber - 2 ; $currentPage < $pageNumber + 3 ; $currentPage++) {
                if($currentPage > 0 && $currentPage <= $totalPages) {
                    $ret .= "<li" . ($currentPage == $pageNumber ? " class=\"active\"" : "") . "><a href=\"" . str_replace("%", $currentPage, $url) . "\" data-page=\"$currentPage\" class=\"" . ($currentPage == $pageNumber ? "active" : "") . "\">$currentPage </a></li>";
                }
            }
            if($pageNumber + 3 <= $totalPages) {
                $ret .= "<li><span class=\"ellipsis\"><i class=\"fa fa-ellipsis-h\"></i></span></li>";
            }
            
            if($totalNumberOfItems / $itemsPerPage <= $pageNumber) {
                $ret .= "<li class=\"disabled\"><a href=\"" . str_replace("%", $pageNumber, $url) . "\">&raquo;</a></li>";
            } else {
                $ret .= "<li title=\"Next Page\"><a href=\"" . str_replace("%", ($pageNumber + 1), $url) . "\" data-page=\"" . ($pageNumber + 1) . "\">&raquo;</a></li>";
            }
            return "$ret</ul>";
        } else {
            return "";
        }
    }
    
    public static function iconsModal() {
        $r = "
        <div class=\"modal fade\" id=\"modal-icons\">
            <div class=\"modal-dialog\">
                <div class=\"modal-content\">
                    <div class=\"modal-header\">
                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>
                        <h4 class=\"modal-title\">Modifier l'icône</h4>
                    </div>
                    <div class=\"modal-body\">
                        <ul class=\"icons\">";
        $icons = Array("bullhorn", "shopping-cart", "music", "file-text-o", "anchor", "archive", "arrows", "asterisk", "ban", "bar-chart-o", "barcode", "beer", "bell-o", "bolt", "book", "briefcase", "bug", "building-o", "calendar", "camera", "clock-o", "cloud", "coffee", "compass", "credit-card", "cutlery", "dashboard", "desktop", "envelope-o", "film", "flag", "flask", "gamepad", "gavel", "gift", "glass", "globe", "headphones", "heart", "home", "key", "laptop", "leaf", "lightbulb-o", "magic", "magnet", "microphone", "moon-o", "pencil", "phone", "plane", "print", "puzzle-piece", "road", "rocket", "suitcase", "tags", "tint", "trophy", "truck", "umbrella", "video-camera", "wrench", "bicycle", "bus", "calculator", "soccer-ball-o", "paint-brush", "newspaper-o", "bed", "diamond", "heartbeat", "motorcycle", "ship", "street-view", "subway", "train", "user-secret", "child");
        sort($icons);
        foreach($icons as $icon) {
            $r .= "<li><button class=\"btn btn-default\" data-value=\"$icon\" title=\"$icon\"><i class=\"fa fa-$icon fa-fw\"></i></button></li>";
        }
        $r .= "
                        </ul>
                    </div>
                </div>
            </div>
        </div>";
        return $r;
    }
    
}
