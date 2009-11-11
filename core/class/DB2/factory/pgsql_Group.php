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

class pgsql_Group extends DB2_Group {

    public function __toString()
    {
        /**
         * Postgresql does not support anything besides the base group by clause
         */
        if (empty($this->fields)) {
            return '';
        }

        foreach ($this->fields as $field) {
            if (is_array($field) && $this->type != DB2_GROUP_SET) {
                throw new PEAR_Exception(dgettext('core', 'Multidimensional arrays prohibited for the current group by type'));
            }
        }
        $query = 'GROUP BY ' . DB2::toStringImplode(', ', $this->fields);

        return $query;
    }

    /**
     * Postgresql does not  permit rollups, cube, or sets
     * @see core/class/DB2/DB2_Group#allowedType($type)
     */
    public function allowedType($type)
    {
        return ($type == DB2_GROUP_BASE);
    }
}
?>