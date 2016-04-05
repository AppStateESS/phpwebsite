<?php

/*
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class PackageManager {
    public static function loadPackage($module)
    {
        $moduleObj = new \Variable\Attribute($module);
        $file_path = 'Module/' . $moduleObj->get() . '/Setup/Package.php';
        $class_name = $moduleObj->get() . '\Setup\Package';
        if (!is_file($file_path)) {
            throw new \Exception(t('Package file does not exist for module "%s"', $module));
        }
        require_once $file_path;
        $package = new $class_name;
        return $package;
    }

}

?>
