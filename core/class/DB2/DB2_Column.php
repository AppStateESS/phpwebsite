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
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

abstract class DB2_Column extends DB2_Alias {
    /**
     * Reference to the parent table object
     * @var object
     */
    public    $resource   = null;
    protected $name     = null;
    protected $splat    = false;

    public function __construct($name, $resource)
    {
        $this->resource = $resource;
        // allow the splat (*) as a column name
        if ( ($this->splat && $name != '*') && !$this->resource->verifyColumn($name)) {
            throw new PEAR_Exception(dgettext('core', 'Column does not exist in this table.'));
        }
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullName()
    {
        return $this->resource . '.' . $this->name;
    }

    public function getTableName()
    {
        return $this->resource->getName();
    }

    /**
     * Returns true if the current object is the same table that is set
     * to this field
     * @param $table
     * @return unknown_type
     */
    public function isTable(DB2_Table $table)
    {
        return $this->resource === $table;
    }
}
?>