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
 * The DB2_Table class is extended by the database OS specific file. For example,
 * mysql_Table extends this table for the MySQL database OS.
 *
 * Many functions used in DB2_Table are contained in DB2_Resource. Please see
 * the functions and comments there for more information.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package DB2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @see DB2_Resource
 */

define('DB2_VERIFY_COLUMNS', true);

abstract class DB2_Table extends DB2_Resource implements Factory_Table {
    /**
     * Name of the table
     * @var string
     * @access protected
     */
    protected $name = null;

    /**
     * Name of table after prefixed
     * @var string
     * @access protected
     */
    protected $full_name   = null;

    /**
     * Array of DB2_Column objects used in updates. This array
     * is a multiple array structure.
     * @var array
     * @access protected
     */
    protected $values      = array();

    /**
     * Array of order by clauses for this table.
     * @var unknown_type
     */
    protected $orders      = array();


    /**
     * An array of having objects.
     * @var array
     */
    protected $having_stack = null;


    /**
     * May contain a DB2 object used for insertion.
     * @var DB2
     */
    protected $insert_select = null;

    /**
     * An array of column names used with an insert select. They must
     * match the order of the select result.
     * @var array
     */
    protected $insert_select_columns = null;

    /**
     * Contains an array of ids incremented from a previous insert
     * @var array
     */
    protected $incremented_ids = null;

    /**
     * Stores the primary index, if exists.
     * @var string
     */
    protected $primary_index = null;


    static public function factory($name, $alias=null, DB2 $db2_ref)
    {
        $dbtype = $db2_ref->mdb2->dbsyntax;
        $file = PHPWS_SOURCE_DIR . "core/class/DB2/factory/{$dbtype}_Table.php";
        if (!is_file($file)) {
            throw new PEAR_Exception(dgettext('core', 'Table functionality is not available for this database type'));
        }
        require_once $file;
        $class_name = $dbtype . '_Table';
        return new $class_name($name, $alias, $db2_ref);
    }

    public function __construct($name, $alias=null, DB2 $db2)
    {
        parent::__construct($db2, $alias);
        $this->setName($name);
        $this->primary_index = $this->getPrimaryIndex();
    }

    public function setName($name)
    {
        if (!$this->db2->allowed($name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper table name') . ': ' . $name);
        }

        if (!$this->db2->tableExists($name)) {
            throw new PEAR_Exception(dgettext('core', 'Table does not exist') . ': ' . $name);
        }
        $this->name = $name;
        $this->full_name = $this->db2->getTablePrefix() . $this->name;
    }

    public function getValues()
    {
        return $this->values;
    }

    /**
     * Adds an associative array of values to the table for an update or
     * insert execution. If this is a multi-tier array, multiple value
     * rows will be added. Note that multiple row is only useful in insertions.
     * Updates will execute the first row ONLY.
     *
     * @param array $values
     * @return void
     */
    public function addValueArray(array $values)
    {
        static $value_key = 0;
        foreach ($values as $key=>$val) {
            if (is_array($val)) {
                foreach ($val as $skey=>$sval) {
                    $this->addValue($skey, $sval, $value_key);
                }
                $value_key++;
            } else {
                $this->addValue($key, $val);
            }
        }
    }

    /**
     * Adds insert or update values to the table. If the column is a value object
     * we add it to the array. The value key allows a multiple value query to be
     * sent to the table. The developer's code must keep track of this number.
     * If you want to send an array of values to the table, use addValueArray.
     * Returns the added value object.
     *
     * @param string|DB2_Value $column
     * @param mixed $value
     * @param integer $value_key
     * @return object
     */
    public function addValue($column, $value=null, $value_key=0)
    {
        $value_key = (int)$value_key;

        if (is_string($column)) {
            $value = $this->getValue($column, $value, $this);
        } elseif (is_a($column, 'DB2_Value')) {
            if (!$column->isTable($this)) {
                throw new PEAR_Exception(dgettext('core', 'Value object referenced different table object'));
                return false;
            }
            $value = $column;
        } else {
            throw new PEAR_Exception(dgettext('core', 'Improper parameter'));
        }

        $this->values[$value_key][$value->getName()] = $value;
        return $value;
    }


    /**
     * Returns a DB2_Value object. If the column is NOT in the table, a
     * PEAR_Exception is thrown by the DB2_Column constructor
     * @param string $column_name
     * @param mixed $value
     * @return DB2_Value
     */
    public function getValue($column_name, $value=null)
    {
        if (!$this->db2->allowed($column_name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper column name'));
        }
        require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Value.php';
        $value = new DB2_Value($column_name, $value, $this);

        return $value;
    }

    public function __toString()
    {
        if ($this->alias) {
            return $this->alias;
        } else {
            return $this->full_name;
        }
    }


    public function saveObjects()
    {
        if (empty($this->object_list)) {
            throw new PEAR_Exception(dgettext('core', 'No objects found to save'));
        }

        //allows multiple object insertions
        $value_key = 0;
        $primary_index = $this->getPrimaryIndex();
        foreach ($this->object_list as $obj)
        {
            if (method_exists($obj, 'DB2Save')) {
                $values = $obj->DB2Save();
            } else {
                $get_object_vars($obj);
            }

            // if true, the user set the primary key
            $index_set = false;
            foreach ($values as $column_name=>$value) {
                if ($this->verifyColumn($column_name)) {
                    // if the current column is the primary index AND it has a value,
                    // the index is set. We do NOT set the value
                    if ($column_name == $primary_index && $value) {
                        $index_set = true;
                        continue;
                    }
                    $this->addValue($column_name, $value, $value_key);
                }
            }

            $value_key++;
        }
        // The index is not set, so we insert
        if (!$index_set) {
            $this->insert();
            $this->resetValues();
        } else {
            // The index was set in the columns, so we update
            $this->db2->update();
            $this->resetValues();
        }
    }

    public function resetValues()
    {
        $this->values = null;
    }

    public function reset()
    {
        parent::reset();
        $this->values                = null;
        $this->orders                = null;
        $this->insert_select         = null;
        $this->insert_select_columns = null;
        $this->having_stack          = null;
        $this->incremented_ids       = null;
    }


    public function insert()
    {
        $query = $this->insertQuery();
        $result = $this->db2->mdb2->exec($query);
        if ($this->db2->pearError($result)) {
            throw new PEAR_Exception($result->getMessage());
        }
        return $result;
    }

    /**
     * Constructs an insertQuery.
     * @return unknown_type
     */
    public function insertQuery()
    {
        /**
         * If insert select is present, we run with it and stop. The columns are ignored below.
         */
        if ($this->insert_select) {
            if (empty($this->insert_select_columns)) {
                return sprintf('insert into %s %s;', $this->getFullName(), $this->insert_select);
            } else {
                return sprintf('insert into %s (%s) %s;', $this->getFullName(), implode(', ', $this->insert_select_columns), $this->insert_select);
            }
        }

        if (empty($this->values)) {
            throw new PEAR_Exception(sprintf(dgettext('core', 'No columns to insert in table: %s'), $this->getFullName()));
        }

        foreach ($this->values as $val_listing) {
            $columns = array();

            if (!isset($set_names)) {
                $set_names = array_keys($val_listing);
                if ($this->primary_index) {
                    $set_names[] = $this->primary_index;
                }
            }
            $primary_key_found = false;
            foreach ($val_listing as $value) {
                // Don't need to create an associative array but
                if ($value->getName() == $this->primary_index) {
                    $primary_key_found = true;
                }
                $columns[] = $value->getValue();
            }

            if ($this->primary_index && !$primary_key_found) {
                // in case the primary key is hard-coded, we check for its existance
                $this->incremented_ids[] = $columns[] = $this->db2->mdb2->nextID($this->getFullName());
            }
            $column_values[] = '(' . implode(', ', $columns) . ')';
        }
        return sprintf('insert into %s (%s) values %s;', $this->getFullName(), implode(', ', $set_names), implode(', ', $column_values));
    }

    /**
     * Returns incremented primary key ids created from previous inserts
     * @return array
     */
    public function getIncrementedIds($first_only=false)
    {
        if (empty($this->incremented_ids)) {
            return null;
        }

        if ($first_only) {
            return current($this->incremented_ids);
        } else {
            return $this->incremented_ids;
        }
    }

    /**
     * Searches the current table for a primary key integer column and returns its name.
     * Returns false if none exists.
     * @return string|boolean
     */
    public function getPrimaryIndex()
    {
        static $primary_index_checked = false;

        // If the primary index is set for the table, return it
        if (!empty($this->primary_index)) {
            return $this->primary_index;
        } elseif ($primary_index_checked) {
            // if the primary_index is empty and it was previously checked, return null
            return null;
        }

        $field_list = $this->listFieldInfo(true);
        if ($this->db2->pearError($field_list)) {
            throw new PEAR_Exception($field_list->getMessage());
        }
        foreach ($field_list as $field) {
            if (strstr($field['flags'], 'primary_key') && $field['type'] == 'int') {
                $this->primary_index = $field['name'] ;
                return $this->primary_index;
            }
        }
        return null;
    }



    /**
     * Verifies the existence of a specific column in this table. The static table_info
     * contains all previous check data.
     * @param string column_name : Column looked for in the table
     * @access public
     */
    public function verifyColumn($column_name)
    {
        if (!DB2_VERIFY_COLUMNS) {
            return true;
        }

        static $table_info = array();

        if (!isset($table_info[$this->db2->mdb2->database_name][$this->full_name])) {
            $fields = $this->listFieldInfo();
            $table_info[$this->db2->mdb2->database_name][$this->full_name] = & $fields;
        }

        return in_array($column_name, $table_info[$this->db2->mdb2->database_name][$this->full_name]);
    }

    public function listFieldInfo($verbose=false)
    {
        if ($verbose) {
            $this->db2->mdb2->loadModule('Reverse');
            $fields = $this->db2->mdb2->tableInfo($this->getFullName());
        } else {
            $this->db2->mdb2->loadModule('Manager');
            $fields = $this->db2->mdb2->listTableFields($this->getFullName());
        }
        if (MDB2::isError($fields)) {
            throw new PEAR_Exception('MDB2::listTableFields error - ' . $fields->getMessage());
        } else {
            return $fields;
        }
    }

    /**
     * Returns table name, not prefixed
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns prefixed table name
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Returns the table identifier for a sql query.
     * @return string
     */
    public function getQuery()
    {
        $this->queried = true;
        if ($this->alias) {
            return "$this->full_name AS $this->alias";
        } else {
            return $this->full_name;
        }
    }

    /**
     * If passed a parameter, it sets the show_all_fields variable.
     * Returns variable condition.
     *
     * @param boolean show
     * @return boolean
     */
    public function showAllFields($show=null)
    {
        if (isset($show)) {
            $this->show_all_fields = (bool)$show;
        }
        return $this->show_all_fields;
    }

    /**
     * Inserts a DB2 object's select result into this table.
     * If no columns are set you would get this result:
     *
     * Example:
     * $db_select = new DB2;
     * $bar = $db_select->addTable('bar');
     *
     * $db = new DB2;
     * $foo = $db->addTable('foo');
     * $foo->insertSelect($db_select);
     *
     * Query:
     * insert into foo SELECT bar.* FROM bar;
     *
     * Using columns names limits the columns inserted.
     *
     * Example using above objects:
     *
     * $bar->addField('id'); // remember: $bar is with $db_select
     *
     * $foo->insertSelect($db_select, array('foo_id'));
     *
     * Query:
     * insert into foo (foo_id) SELECT bar.id FROM bar;
     *
     * @param DB2 $db2 The database object from which we grab a select result
     * @param array $column_names Names of columns to insert into
     * @return void
     */
    public function insertSelect(DB2 $db2, $column_names=null)
    {
        if ($db2 === $this->db2) {
            throw new PEAR_Exception(dgettext('core', 'The insert select DB2 object must not be this object\'s current parent'));
        }

        $this->insert_select = $db2;

        if (is_array($column_names)) {
            $this->insert_select_columns = $column_names;
        }
    }
}

?>