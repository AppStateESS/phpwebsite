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
 * The most basic method for importing and exporting your object to the database.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @see Data
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

abstract class DB2_Object extends Data implements DB2_Object_Interface {

    public function setObjectVars(array $values) {
        foreach ($select_row as $key=>$value) {
            $this->$key = $value;
        }
    }

    public function DB2Load(array $select_row)
    {
        $this->setObjectVars($select_row);
    }

    public function DB2Save()
    {
        return get_object_vars($this);
    }
}


?>