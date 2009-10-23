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
 * A DB2_Resource is an database entity from which information is derived or
 * manipulated. A table is an example and a database subselect is another.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

abstract class DB2_Resource extends DB2_Alias {
    /**
     * Database object related to this object
     * @var object
     * @access public
     */
    public $db2 = null;

    /**
     * Fields requested in a select query
     * @var array
     * @access protected
     */
    protected $fields = array();

    /**
     * If true, then a select query will call all rows of this object.
     * @var boolean
     */
    protected $show_all_fields = true;

    /**
     * An array of conditional objects. This array will be emptied as conditional objects
     * are placed into the db2 object
     */
    protected $where_stack = array();

    /**
     * Indicates if this resource is being used in a join.
     * @var boolean
     * @access protected
     */
    protected $joined = false;

    /**
     * If true, order by will be ignored and a random sample will be used instead
     * @var boolean
     */
    protected $random_order = false;

    abstract public function verifyColumn($column_name);
    abstract public function getQuery();

    public function __construct(DB2 $db2, $alias=null)
    {
        $this->db2 = $db2;
        $this->setAlias($alias);
    }

    public function getFields()
    {
        if (empty($this->fields)) {
            if ($this->show_all_fields) {
                if ($this->alias) {
                    return $this->getAlias() . '.*';
                } else {
                    return $this->full_name . '.*';
                }
            }
        } else {
            foreach ($this->fields as $field) {
                if ($field->showInSelect()) {
                    $cols[] = $field;
                }
            }
            if (isset($cols)) {
                return implode(', ', $cols);
            }
        }
        return null;
    }

    /**
     * Adds a field object to the table object's field stack.
     * @param string|DB2_Field $column_name    If not a DB2_Field object, then the name of the column in the table
     * @param string           $alias          An alias to be used within the query for this field.
     * @param boolean          $show_in_select If true, show in a select query. False, otherwise.
     * @return DB2_Field
     */
    public function addField($column_name, $alias=null, $show_in_select=true)
    {
        if (is_string($column_name)) {
            $field = $this->getField($column_name, $alias, $this);
        } elseif (is_a($column_name, 'DB2_Field')) {
            if (!$column_name->isTable($this)) {
                throw new PEAR_Exception(dgettext('core', 'Field object referenced different table object'));
                return false;
            }
            $field = $column_name;
        } else {
            throw new PEAR_Exception(dgettext('core', 'Improper parameter'));
        }
        $field->showInSelect($show_in_select);
        $this->fields[] = $field;
        return $field;
    }

    /**
     * Returns a DB2_Field object. If the column is NOT in the table, a
     * PEAR_Exception is thrown by the DB2_Field constructor
     * @param string $column_name Name of the column in this table
     * @param string $alias       Query alias used when referencing this field
     * @return DB2_Field
     */
    public function getField($column_name, $alias=null)
    {
        if (!$this->db2->allowed($column_name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper column name'));
        }
        require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Field.php';
        $field = new DB2_Field($column_name, $alias, $this);

        return $field;
    }


    public function addWhereArray(array $where_array)
    {
        foreach ($where_array as $col=>$val) {
            $ret_array = $this->addWhere($col, $val);
        }
        return $ret_array;
    }

    /**
     * Creates a new DB2_Conditional object into and places it on the where_stack.
     *
     * @param string|object $column : Name of table column or a conditional object
     * @param string $value
     * @param string $operation
     * @return object
     */
    public function addWhere($column, $value, $operator=null, $conjunction='AND')
    {
        static $stack_number = 0;

        /**
         * Prevents endless recursion
         */
        if ($value === $this->db2) {
            throw new PEAR_Exception(dgettext('core', 'Embedding the parent DB2 object in a conditional is forbidden'));
            return false;
        }
        if (is_a($column, 'DB2_Conditional')) {
            if ($column->table != $this) {
                throw new PEAR_Exception(dgettext('core', 'Conditional object referenced incorrect table object'));
                return false;
            }
            $where = $column;
        } else {
            $where = $this->getConditional($column, $value, $operator, $conjunction);
        }
        $where->stack_number = $stack_number;
        $this->where_stack[$stack_number] = $where;
        $stack_number++;
        return $where;
    }

    public function getWhereStack($conjunction=true)
    {
        if (!empty($this->where_stack)) {
            foreach ($this->where_stack as $w) {
                if (!$conjunction) {
                    $w->disableConjunction();
                    $conjunction = true;
                }
                $where_list[] = $w;
            }
        }

        if (isset($where_list)) {
            return implode(' ', $where_list);
        }
    }

    public function getConditional($column, $value, $operator=null, $conjunction='AND')
    {
        $conditional = DB2_Conditional::factory($column, $value, $operator, $this);
        $conditional->setConjunction($conjunction);
        return $conditional;
    }

    public function dropFromWhereStack($stack_number)
    {
        unset($this->where_stack[$stack_number]);
    }

    public function setJoined($joined)
    {
        $this->joined = (bool)$joined;
    }

    public function getDBType()
    {
        return $this->db2->mdb2->dbsyntax;
    }

    public function addOrderBy($column, $direction=null)
    {
        if (empty($direction)) {
            $direction = 'ASC';
        } else {
            $direction = trim(strtoupper($direction));
        }

        if ($direction != 'ASC' && $direction != 'DESC') {
            throw new PEAR_Exception(dgettext('core', 'Unknown order direction'));
        }

        if ($this->verifyColumn($column)) {
            $this->orders[] = "$this.$column $direction";
        } else {
            throw new PEAR_Exception(dgettext('core', 'Unknown table column'));
        }
    }

    public function getOrderBy()
    {
        if (empty($this->orders)) {
            return null;
        } else {
            return implode(', ', $this->orders);
        }
    }

    /**
     * If true, the return order will be randomized
     * @return unknown_type
     */
    public function randomOrder($order=true)
    {
        $this->random_order = (bool)$order;
    }

    /**
     * Returns state of random order
     * @return boolean
     */
    public function isRandomOrder()
    {
        return $this->random_order;
    }

    public function isJoined()
    {
        return $this->joined;
    }

    public function addHaving($column, $value, $operator=null)
    {
        static $stack_number = 0;

        /**
         * Prevents endless recursion
         */
        if ($value === $this->db2) {
            throw new PEAR_Exception(dgettext('core', 'Embedding the parent DB2 object in a conditional is forbidden'));
            return false;
        }
        if (is_a($column, 'DB2_Conditional')) {
            if ($column->table != $this) {
                throw new PEAR_Exception(dgettext('core', 'Having object referenced incorrect table object'));
                return false;
            }
            $having = $column;
        } else {
            $having = $this->getConditional($column, $value, $operator);
        }
        $having->stack_number = $stack_number;
        $this->having_stack[$stack_number] = $having;
        $stack_number++;
        return $having;
    }

    public function getHavingStack($conjunction=true)
    {
        if (!empty($this->having_stack)) {
            foreach ($this->having_stack as $w) {
                if (!$conjunction) {
                    $w->disableConjunction();
                    $conjunction = true;
                }
                $having_list[] = $w;
            }
        }

        if (isset($having_list)) {
            return implode(' ', $having_list);
        }
    }

    public function dropFromHavingStack($stack_number)
    {
        unset($this->having_stack[$stack_number]);
    }

}

?>