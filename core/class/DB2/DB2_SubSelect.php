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
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
class DB2_SubSelect extends DB2_Resource {

    /**
     * Constructs the SubSelect object. Unlike DB2_Resource, as is required.
     * @param $db2
     * @param $alias
     * @return unknown_type
     */
    public function __construct(DB2 $db2, $alias)
    {
        parent::__construct($db2, $alias);
    }

    public function verifyColumn($column_name)
    {
        return true;
        $all_tables = $this->db2->getAllTables();
        foreach ($all_tables as $table) {
            if ($table->verifyColumn($column_name)) {
                return true;
            }
        }
        return false;
    }

    public function __toString()
    {
        return $this->alias;
    }

    public function getQuery()
    {
        return sprintf('(%s) AS %s', $this->db2->selectQuery(), $this->getAlias());
    }
}
?>