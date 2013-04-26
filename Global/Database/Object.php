<?php

namespace Database;

/**
 * The most basic method for importing and exporting your object to the database.
 *
 * There are two required functions to create in the parent object: pullValues
 * and pushValues. See the functions below for more information.
 *
 * The parent function must set the table in the construct.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @see \Data
 * @todo This may be a relic of 1.x. May not be needed. Combine with Resource or
 * delete
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Object extends Data {

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
     * DB object created before operating on object
     * @var object
     */
    private $DB = null;
    /**
     * Table object created with database
     * @var object
     */
    private $table = null;
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

    protected function setPrimaryKeyColumn($column_name)
    {
        $this->primary_key_column = $column_name;
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
     * we want to allow the saveObject function in DB to be able to populate those variables
     * to prevent repeated instantiations.     */
    private function loadDatabase()
    {
        if (isset($this->DB)) {
            return;
        }

        $this->DB = \Database::newDB();
        if (empty($this->table_name)) {
            throw new \Exception(t('Missing table name'));
        }

        if (!$this->DB->tableExists($this->table_name)) {
            throw new \Exception(sprintf(t('Table does not exist: %s'), $this->table_name));
        }
        $this->table = $this->DB->addTable($this->table_name);
        if (empty($this->primary_key_column)) {
            $primary_index = $this->table->getPrimaryIndex();
            if ($primary_index) {
                $this->primary_key_column = $primary_index;
            } else {
                throw new \Exception(t('Object could not derive the table primary index'));
            }
        }
        $this->primary_key = & $this->{$this->primary_key_column};
    }

    /**
     * Inserts or updates an object according to its table_name setting.     */
    public function save()
    {
        $this->loadDatabase();
        $this->loadObjectValues();

        if (empty($this->object_values)) {
            throw new \Exception(t('Object does not contain any values to save'));
        }

        foreach ($this->object_values as $col_name => $val) {
            $this->table->addValue($col_name, $val);
        }

        // if id exists, this is an old object
        if ($this->primary_key) {
            // old object, update
            $this->new_object = false;
            $this->table->addWhere($this->primary_key_column, $this->primary_key);
            $this->DB->update();
        } else {
            // new object, insert
            $this->new_object = true;
            $this->DB->insert();
            $this->primary_key = $this->table->getIncrementedIds(true);
        }
    }

    public function load()
    {
        $this->loadDatabase();
        // primary key is loaded by above function
        if (!$this->primary_key) {
            throw new \Exception(t('Cannot load object without primary key set'));
        }

        $this->table->addWhere($this->primary_key_column, $this->primary_key);
        try {
            $result = $this->DB->select(ROW);
        } catch (Error $e) {
            $this->DB->logError($e);
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
            throw new \Exception(t('Cannot load object without primary key set'));
        }
        $this->table->addWhere($this->primary_key_column, $this->primary_key);
        $this->DB->delete();
    }

}

?>