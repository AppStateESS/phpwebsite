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

require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Column.php';

class DB2_Value extends DB2_Column {
    private $value    = null;

    public function __construct($name, $value=null, DB2_Table $table)
    {
        parent::__construct($name, $table);
        $this->setValue($value);
    }

    public function setValue($value)
    {
        if (is_string($value) || is_integer($value)) {
            $this->value = $this->table->db2->mdb2->quote($value);
        } elseif (is_a($value, 'DB2_Expression') || is_a($value, 'DB2_Function') || is_a($value, 'DB2_Field')) {
            $this->value = $value;
        } elseif (is_null($value)) {
            $this->value = 'NULL';
        } else {
            throw new PEAR_Exception(dgettext('core', 'Unacceptable value'));
        }
    }

    /**
     * Returns a value string for use in update executions. Insert does
     * not use this function because the label and value are split from
     * one another.
     * @return string
     */
    public function __toString()
    {
        if (is_a($this->value, 'DB2_Field')) {
            return $this->getFullName() . '=' . $this->value->getFullName();
        } else {
            return $this->getFullName() . "={$this->value}";
        }
    }

    public function getValue()
    {
        return $this->value;
    }
}

?>