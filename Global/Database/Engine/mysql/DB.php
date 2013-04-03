<?php

namespace Database\Engine\mysql;

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

define('MYSQL_COLLATE', 'utf8_general_ci');
define('MYSQL_CHARACTER_SET', 'utf8');

class DB extends \Database\DB {

    public function getDelimiter()
    {
        return '`';
    }

    /**
     * Returns true if the table name exists in the database.
     * To see if the table is currently in the DB object stack,
     * @see DB::isTable($table_name)
     * @param string $table_name Name of table to check
     * @return boolean
     */
    public function tableExists($table_name)
    {
        if ($this->hasPrefix()) {
            $table_name = $this->getTablePrefix() . $table_name;
        }
        $this->loadStatement("SHOW TABLES like '$table_name'");
        $result = $this->fetchRow();
        return (bool) $result;
    }

    public function databaseExists($database_name)
    {
        $this->loadStatement("SELECT schema_name FROM `information_schema`.`SCHEMATA` WHERE SCHEMA_NAME='$database_name'");
        return (bool) $this->fetchRow();
    }

    public function listTables()
    {
        $table_list = null;
        $this->loadStatement('show tables');

        while($result = $this->fetchColumn()) {
            $table_list[] = $result;
        }
        return $table_list;
    }

    public function createDatabase($database_name)
    {
        if (!$this->allowedIdentifier($database_name)) {
            throw new \Exception(t('Improper database name'));
        }

        return $this->query("CREATE DATABASE $database_name COLLATE " . MYSQL_COLLATE . ' CHARACTER SET ' . MYSQL_CHARACTER_SET);
    }
}

?>
