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
 * @param string $file Full path to file
 * @return string Returns the octal notation of the file
 */
function get_file_permission($file)
{
    $stat = stat($file);
    return sprintf("%o", ($stat['mode'] & 000777));
}

/**
 * Requires a file without echoing the content
 * @param string $file
 * @return string If the file is not php, returns the result.
 */
function safeRequire($file, $once = true)
{
    return safeFile($file, $once ? 'require_once' : 'require');
}

/**
 * Requires a file without echoing the content
 * @param string $file
 * @return string If the file is not php, returns the result.
 */
function safeInclude($file, $once = true)
{
    return safeFile($file, $once ? 'include_once' : 'include');
}

/**
 * Includes or requires a file. Returns an array of variables defined in the file
 * and any content echoed within.
 * @param string $file Path to file
 * @param string $type Type of require or include to use
 * @return array
 */
function safeFile($file, $type = 'require')
{
    ob_start();
    switch ($type) {
        case 'include':
            include $file;
            break;
        case 'include_once':
            include $file;
            break;
        case 'require':
            require $file;
            break;
        case 'require_once':
            require_once $file;
            break;
    }
    unset($file);
    unset($type);
    $arr['variables'] = get_defined_vars();
    $arr['string'] = ob_get_contents();
    ob_end_clean();
    return $arr;
}
