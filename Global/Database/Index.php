<?php

namespace Database;

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
 * Assists with creating an index on a table. Because not all databases allow
 * index creation during table creation (Postgresql for example), indexes are
 * created separately.
 *
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Index extends Constraint {
    private $is_unique = false;

    public function __construct($column, $name)
    {
        $this->setName($name);
        $this->setColumns($column);
    }

    public function getConstraintType()
    {
        return 'INDEX';
    }

    public function setIsUnique($unique=true)
    {
        $this->is_unique = (bool)$unique;
    }

    public function createQuery()
    {
        $sql[] = 'CREATE';
        if ($this->is_unique) {
            $sql[] = 'UNIQUE';
        }
        $sql[] = 'INDEX';
        $sql[] = $this->name;
        $sql[] = 'ON ' . $this->getSourceTable();
        $sql[] = $this->getColumnKeysString();

        return implode(' ', $sql);
    }

    public function create()
    {
        $query = $this->createQuery();
        return $this->source_table->db->exec($query);
    }

}

?>
