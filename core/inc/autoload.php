<?php
namespace Core;
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
spl_autoload_register(null, false);
spl_autoload_extensions('.php');

function autoload($class_name)
{
    //if using namespace
    if (strstr($class_name, '\\')) {
        $class_name = substr(strrchr($class_name, '\\'), 1);
    }
    $filename = $class_name . '.php';
    $file = PHPWS_SOURCE_DIR . 'core/class/' . $filename;
    if (!is_file($file)) {
        $filename = str_replace('PHPWS_', '', $class_name) . '.php';
        $file = PHPWS_SOURCE_DIR . 'core/class/' . $filename;
        if (!is_file($file)) {
            return false;
        }
    }
    require_once $file;
    return true;
}

spl_autoload_register('Core\autoload');

?>