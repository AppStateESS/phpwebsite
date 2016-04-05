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
 *
 * Shorthand function to translate using the Language class. Gets domain
 * from the class passed in the backtrace. IF dgettext is not compiled into PHP
 * the arguments are just returned with sprintf.
 * @return string
 * @see Language::translate()
 */
function t()
{
    static $lang = null;
    $args = func_get_args();

    if (!function_exists('dgettext')) {
        if (count($args) > 1) {
            return call_user_func_array('sprintf', $args);
        } else {
            return $args[0];
        }
    }
    if (empty($lang)) {
        $lang = new \Language;
    }

    $r = debug_backtrace();
    $file_path = $r[0]['file'];
    if (strstr($file_path, 'mod/')) {
        $domain = preg_replace('|.*mod/([^/]+)/.*|', '\\1', $file_path);
    } else {
        $domain = 'core';
    }
    $lang->setDomain($domain);
    return $lang->translate($args);
}
