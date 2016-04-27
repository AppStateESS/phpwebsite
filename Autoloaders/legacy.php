<?php

/*
 * Copyright (C) 2016 Matthew McNaney <mcnaneym@appstate.edu>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

/**
 * @author Matthew McNaney <mcnaneym at appstate dot edu>
 */
function LegacyLoader($class_name)
{
    // stores previously found requires
    static $files_found = array();

    if (isset($files_found[$class_name])) {
        // If class was found, we require and move on
        require_once $files_found[$class_name];
        return;
    }
    $class_name = preg_replace('@^/|/$@', '', str_replace('\\', '/', $class_name));
    $new_mod_file = PHPWS_SOURCE_DIR . preg_replace('|^([^/]+)/([\w/]+)|', 'mod/\\1/class/\\2.php', $class_name);
    $global_file = PHPWS_SOURCE_DIR . 'Global/' . $class_name . '.php';
    $class_file = PHPWS_SOURCE_DIR . 'core/class/' . $class_name . '.php';
    if (is_file($new_mod_file)) {
        $files_found[$class_name] = $new_mod_file;
        require_once $new_mod_file;
        return true;
    } elseif (is_file($global_file)) {
        $files_found[$class_name] = $global_file;
        require_once $global_file;
        return true;
    } elseif (is_file($class_file)) {
        $files_found[$class_name] = $class_file;
        require_once $class_file;
        return true;
    } elseif (isset($_REQUEST['module'])) {
        $module = preg_replace('/\W/', '', $_REQUEST['module']);

        if (preg_match("/^$module\//i", $class_name)) {
            $class_name = preg_replace("/^$module\//i", '', $class_name);
        }

        $class_file = PHPWS_SOURCE_DIR . "mod/$module/class/$class_name.php";

        if (is_file($class_file)) {
            $files_found[$class_name] = $class_file;
            require_once $class_file;
            return true;
        } else {
            return false;
        }
    }
}
