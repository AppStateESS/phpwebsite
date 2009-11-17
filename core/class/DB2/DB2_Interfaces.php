<?php
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
 * These interfaces assure compatibility between different database operating systems
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

interface Factory_Table {
    // proper random return dependent on dbos
    public function getRandomOrder();
}


interface DB2_Object_Interface {
    public function save();
    public function load();
    public function delete();
}
?>