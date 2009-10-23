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
 * @package
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class mysql_Group extends DB2_Group {

    public function __toString()
    {
        /**
         * MySQL does not support grouping sets, so multidimensional arrays
         * are forbidden. This foreach tests for that.
         */
        if (empty($this->fields)) {
            return '';
        }

        foreach ($this->fields as $field) {
            if (is_array($field) && $this->type != DB2_GROUP_SET) {
                throw new PEAR_Exception(dgettext('core', 'Multidimensional arrays prohibited for the current group by type'));
            }
        }
        $query = 'GROUP BY ' . implode(', ', $this->fields);

        if ($this->type == DB2_GROUP_ROLLUP) {
            $query .= ' WITH ROLLUP';
        }
        return $query;
    }

    /**
     * MySQL permits standard grouping and rollups. Cube and sets are
     * not supported.
     * @see core/class/DB2/DB2_Group#allowedType($type)
     */
    public function allowedType($type)
    {
        if ($type == DB2_GROUP_BASE || $type == DB2_GROUP_ROLLUP) {
            return true;
        }
        return false;
    }
}
?>