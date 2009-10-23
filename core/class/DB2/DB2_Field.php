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
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package DB2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Column.php';

class DB2_Field extends DB2_Column {

    private $position = 0;
    private $show_in_select = true;
    protected $splat  = true;

    public function __construct($name, $alias=null, $resource)
    {
        parent::__construct($name, $resource);

        if ($alias) {
            $this->setAlias($alias);
        }
    }

    /**
     * Determines if the field should be shown in a select query. For example,
     * if you are using the fields in a function, you may not wish to have
     * them repeated in the query.
     * @param bool $show
     * @return boolean
     */
    public function showInSelect($show=null)
    {
        if (isset($show)) {
            $this->show_in_select = (bool)$show;
        }
        return $this->show_in_select;
    }

    /**
     * Adds this field to the parent table's field stack
     */
    public function addToResource()
    {
        $this->resource->addField($this);
    }

    public function __toString()
    {
        if ($this->alias) {
            return $this->getFullName() . " AS $this->alias";
        } else {
            return $this->getFullName();
        }
    }
}

?>