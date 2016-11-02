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
 * LegacySrcLoader
 * Responsible for loading legacy phpws core classes in the '\phpws' namespace
 * from the /src-phpws-legacy directory.
 *
 * @author Matthew McNaney <mcnaneym at appstate dot edu>
 * @package Canopy
 */
function LegacySrcLoader($class_name)
{
    // Class name must start with the 'phpws\' namespace. If not, we pass and hope another autoloader can help
    if(substr($class_name, 0, strlen('phpws\\')) !== 'phpws\\'){
        return false;
    }

    $file_path = PHPWS_SOURCE_DIR . 'src-phpws-legacy/src/' . str_replace('\\', '/', str_replace('phpws\\', '', $class_name)) . '.php';

    if (is_readable($file_path)) {
        require_once $file_path;
        return true;
    } else {
        return false;
    }
}
