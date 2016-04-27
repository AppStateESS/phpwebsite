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
function CanopyLoader($class_name)
{
    static $files_found = array();

    if (isset($files_found[$class_name])) {
        return;
    }

    $class_array = explode('\\', $class_name);
    $class_dir = array_shift($class_array);

    $base_dir = PHPWS_SOURCE_DIR . "src/$class_dir/autoload.php";

    if (is_file($base_dir)) {
        require_once $base_dir;
        return true;
    } else {
        return false;
    }
}
