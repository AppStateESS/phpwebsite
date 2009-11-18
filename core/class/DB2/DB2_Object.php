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
 * The most basic method for importing and exporting your object to the database.
 *
 * There are two required functions to create in the parent object: pullValues and pushValues.
 * See the functions below for more information.
 *
 * The parent function also must set the table the construct.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @see Data
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

require_once PHPWS_SOURCE_DIR . 'core/class/Data.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Interfaces.php';

abstract class DB2_Object extends Data implements DB2_Object_Interface {

    /**
     * Reference to the parent object's primary key.
     * @var integer
     */
    private $primary_key = null;

    /**
     * Name of the table column housing the primary id
     * @var string
     */
    private $primary_key_column = null;

    /**
     * A temporary array of values used in when an object is saved.
     * @var array
     */
    private $object_values = array();

    /**
     * The table the object uses for information.
     * @var string
     */
    private $table_name = null;


    /**
     * DB2 object created before operating on object
     * @var object
     */
    private $db2 = null;

    /**
     * DB2_Table object created with database
     * @var object
     */
    private $db2_table = null;

    /**
     * An array of foreign keys associated to this item. Should this object be deleted, the
     * corresponding row will as well.
     * The associative array will use table names as keys and foreign keys as values.
     *
     * array ('table2'=>'tb1_id',
     *        'table3'=>'tbl_id');
     *
     * The foreign key will be compared to the primary_id_column.
     *
     * @var array
     */
    private $foreign_keys = null;


    /**
     * If this value is set with an associate array, the foreign keyed value will
     * be updated when this object is updated, but not created.
     *
     * This value will be a multiple dimensioned array using the table name as the key.
     * The associative array under it will have the column name on the table that is
     * to be updated. The key's value will be copied to the column.
     * @var array
     */
    private $foreign_update = null;

    /**
     * Indicates if the current object is new (to be inserted) or old (to be updated).
     * Regardless of action taken on object, this status is retained until reloaded.
     * @var boolean
     */
    private $new_object = true;

    /**
     * Pulls the values from the current object and copies them to the $values.
     * Parent object will need to create it. The simplest method would be:
     *
     * protected function pullValues()
     * {
     *    return get_object_vars($this);
     * }
     *
     */
    abstract protected function pullValues();

    /**
     * Pushes the values from a database retrieval into the parent object.
     * The below is a simple example of what is expected.
     *
     * protected function pushValues(array $values)
     * {
     *     foreach ($values as $key=>$val) {
     *         $this->$key = $val;
     *     }
     * }
     */
    abstract protected function pushValues(array $values);

    /**
     * Sets the table name the object uses.
     * @param unknown_type $table_name
     * @return unknown_type
     */
    protected function setTable($table_name)
    {
        $this->table_name = $table_name;
    }

    public function isNew()
    {
        return $this->new_object;
    }

    private function loadObjectValues()
    {
        $this->object_values = $this->pullValues();
        //removes the id column to be inserted later or used as a conditional
        unset($this->object_values['id']);
    }

    /**
     * Loads the database object and table object. This is not done at construct because
     * we want to allow the saveObject function in DB2 to be able to populate those variables
     * to prevent repeated instantiations.
     * @return void
     */
    private function loadDatabase()
    {
        if (isset($this->db2)) {
            return;
        }

        $this->db2 = new DB2;
        if (empty($this->table_name)) {
            throw new PEAR_Exception(dgettext('core', 'Missing table name'));
        }

        if (!$this->db2->tableExists($this->table_name)) {
            throw new PEAR_Exception(sprintf(dgettext('core', 'Table does not exist: %s'), $this->table_name));
        }
        $this->db2_table = $this->db2->addTable($this->table_name);
        $primary_index = $this->db2_table->getPrimaryIndex();
        if ($primary_index) {
            $this->primary_key_column = $primary_index;
            $this->primary_key = & $this->{$this->primary_key_column};
        }
    }


    /**
     * Inserts or updates an object according to its table_name setting.
     * @return void
     */
    public function save()
    {
        $this->loadDatabase();
        $this->loadObjectValues();

        if (empty($this->object_values)) {
            throw new PEAR_Exception(dgettext('core', 'DB2_Object does not contain any values to save'));
        }

        foreach ($this->object_values as $col_name=>$val) {
            $this->db2_table->addValue($col_name, $val);
        }

        // if id exists, this is an old object
        if ($this->primary_key) {
            // old object, update
            $this->new_object = false;
            $this->db2_table->addWhere($this->primary_key_column, $this->primary_key);
            $this->db2->update();
        } else {
            // new object, insert
            $this->new_object = true;
            $this->db2->insert();
            $this->primary_key = $this->db2_table->getIncrementedIds(true);
        }
    }

    public function load()
    {
        $this->loadDatabase();
        // primary key is loaded by above function
        if (!$this->primary_key) {
            throw new PEAR_Exception(dgettext('core', 'Cannot load object without primary key set'));
        }

        $this->db2_table->addWhere($this->primary_key_column, $this->primary_key);
        try {
            $result = $this->db2->select(DB2_ROW);
        } catch (PEAR_Exception $e) {
            $this->db2->logError($e);
        }
        if (empty($result)) {
            return false;
        } else {
            $this->pushValues($result);
        }
    }

    public function delete()
    {
        $this->loadDatabase();
        // primary key is loaded by above function
        if (!$this->primary_key) {
            throw new PEAR_Exception(dgettext('core', 'Cannot load object without primary key set'));
        }
        $this->db2_table->addWhere($this->primary_key_column, $this->primary_key);
        $this->db2->delete();
    }
}


?>