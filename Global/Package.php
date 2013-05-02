<?php

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

abstract class Package {

    protected $title;
    protected $version;
    protected $tables;
    protected $database_specific;
    protected $dependency;
    protected $create_tables = true;
    protected $remove_tables = true;

    public function __construct($title, $version)
    {
        $this->title = new \Variable\String($title, 'title');
        $this->version = new \Variable\Version($version, 'version');
    }

    public function disableTableCreation()
    {
        $this->create_tables = false;
    }

    public function disableTableRemoval()
    {
        $this->remove_tables = false;
    }


    public function isInstalled()
    {
        $db = \Database::newDB();
        $modules = $db->addTable('Modules');
        $modules->addWhere('title', $this->title);
        $db->loadSelectStatement();
        $result = $db->fetch();
        return !empty($result);
    }

    public function setTitle($title)
    {
        $this->title->set($title);
    }

    public function setVersion($version)
    {
        $this->version->set($version);
    }

    /**
     * Creates a new table object, places it on the table stack, and returns it.
     * When creating foreign keys, the order of the addition is very important.
     * The parent tables must be added prior to the child tables.
     * @param string $table_name
     * @return \Database\Table
     */
    public function addTable($table_name)
    {
        $db = \Database::newDB();
        $table = $db->buildTable($table_name);
        $this->tables[$table->getName()] = $table;
        return $table;
    }

    public function createTables()
    {
        if (empty($this->tables)) {
            throw new \Exception(t('No tables to create'));
        }

        foreach ($this->tables as $tbl) {
            if (!$tbl->exists()) {
                $tbl->create();
            }
        }
    }

    /**
     *
     * @param boolean $create_tables If true, create all the tables in the stack.
     */
    public function install()
    {
        if ($this->create_tables && !empty($this->tables)) {
            $this->createTables();
        }

        $db = \Database::newDB();
        $tbl = $db->addTable('Modules');
        $tbl->addValue('title', $this->title->get());
        $tbl->addValue('version', $this->version->get());
        $tbl->insert();
    }

    /**
     *
     * @param boolean $drop_tables If true, drop all the tables in the stack.
     */
    public function uninstall()
    {
        if ($this->remove_tables && !empty($this->tables)) {
            $this->dropTables();
        }
        $db = \Database::newDB();
        $tbl = $db->addTable('Modules');
        $tbl->addWhere('title', $this->title->get());
        $db->delete();
    }

    /**
     * Drops all the tables in the package. Reverses the order in case there
     * are foreign keys.
     * @throws \Exception
     */
    public function dropTables()
    {
        if (empty($this->tables)) {
            throw new \Exception(t('No tables to drop'));
        }

        $tables_drop = array_reverse($this->tables);
        foreach ($tables_drop as $tbl) {
            $tbl->drop();
        }
    }

}

?>
