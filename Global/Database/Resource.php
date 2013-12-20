<?php

namespace Database;

/**
 * A Resource is an database entity from which information is derived or
 * manipulated. A table is an example and a database subselect is another.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @abstract
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Resource extends Alias {

    /**
     * Database object related to this object
     * @var \Database\DB
     * @access public
     */
    public $db = null;

    /**
     * Fields requested in a select query
     * @var array
     * @access protected
     */
    protected $fields = array();

    /**
     * If true, then a select or delete query will include the table in the query.
     * @var boolean
     */
    protected $use_in_query = true;

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

    /**
     * @return string A string representing the contents of this resource
     */
    abstract public function getResourceQuery();

    /**
     * @param \DB $db Database object this resource is part of
     * @param string $alias Pseudonym for this resource
     */
    public function __construct(\Database\DB $db, $alias = null)
    {
        $this->db = $db;
        $this->setAlias($alias);
    }

    /**
     * @see \Database\Resource::$joined
     * @param boolean $joined
     */
    public function setJoined($joined)
    {
        $this->joined = (bool) $joined;
    }

    /**
     * Set a column to order by and the direction to do so.
     * Update 2013-12-06 : originally Field only, didn't make sense not to
     * use a string as well because the order is supposed to specific to this
     * table.
     * @param mixed $column
     * @param string $direction Either ASC or DESC
     */
    public function addOrderBy($column, $direction = 'ASC')
    {
        if (!($column instanceof \Database\Field) && is_string($column)) {
            $column = $this->getField($column);
        }

        static $allowed_directions = array('ASC', 'DESC', 'RAND', 'RANDOM');
        $direction = trim(strtoupper($direction));

        if (!in_array($direction, $allowed_directions)) {
            throw new \Exception(t('Unknown order direction: %s', $direction));
        }

        // If a random call, return the db os specific function call.
        if ($direction == 'RAND' || $direction == 'RANDOM') {
            $direction = $this->db->getRandomCall();
        }

        if (DATABASE_CHECK_COLUMNS && !$this->columnExists($column)) {
            throw new \Exception(t('Table column "%s" is not known', $column));
        } else {
            if ($column->hasAlias()) {
                $this->orders[] = $column->getAlias() . " $direction";
            } else {
                $this->orders[] = $column->getFullName() . " $direction";
            }
        }
        return $this;
    }

    public function resetOrderBy()
    {
        $this->orders = null;
    }

    /**
     * Returns all the ordered queued in the orders parameters as a string.
     * @return string
     */
    public function getOrderBy()
    {
        if (empty($this->orders)) {
            return null;
        } else {
            return implode(', ', $this->orders);
        }
    }

    /**
     * @param boolean $order If true, the return order will be randomized
     * @return unknown_type
     */
    public function randomOrder($order = true)
    {
        $this->random_order = (bool) $order;
    }

    /**
     * Returns state of random order
     * @return boolean
     */
    public function isRandomOrder()
    {
        return $this->random_order;
    }

    /**
     * @return boolean True is previously joined in a query
     */
    public function isJoined()
    {
        return $this->joined;
    }

    /**
     * Adds a having conditional to the resource. Using having when comparing
     * aggregates (sum, avg, max, etc.).
     * @staticvar int $stack_number
     * @param \Database\Column $column Column to compare
     * @param string $value Value to compare against
     * @param string $operator Comparison operator (=, >, !=)
     * @return \Database\Conditional
     */
    public function addHaving($column, $value, $operator = null)
    {
        static $stack_number = 0;

        /**
         * Prevents endless recursion
         */
        if ($value === $this->db) {
            throw new \Exception(t('Embedding the parent DB object in a conditional is forbidden'));
            return false;
        }
        if ($column instanceof Conditional) {
            if ($column->table != $this) {
                throw new \Exception(t('Having object referenced incorrect table object'));
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

    /**
     * Returns the having conditional stack as a string for use in the database
     * query
     * @param boolean $conjunction
     * @return string
     */
    public function getHavingStack($conjunction = true)
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

    /**
     * Removes a member of the having Conditional object stack
     * @param integer $stack_number
     */
    public function dropFromHavingStack($stack_number)
    {
        unset($this->having_stack[$stack_number]);
    }

    /**
     * Resets the object to its initial parameter state.
     */
    public function reset()
    {
        $this->fields = null;
        $this->having_stack = null;
        $this->joined = null;
        $this->orders = null;
        $this->random_order = false;
        $this->use_in_query = true;
        $this->where_stack = null;
    }

    /**
     * Adds a field object to the table object's field stack.
     * @param mixed $column_name    If not a Field object, then the name of the column in the table or Expression
     * @param string           $alias          An alias to be used within the query for this field.
     * @param boolean          $show_in_select If true, show in a select query. False, otherwise.
     * @return \Database\Field
     */
    public function addField($column_name, $alias = null, $show_in_select = true)
    {
        if (is_string($column_name)) {
            $field = $this->getField($column_name, $alias, $this);
            $field->showInSelect($show_in_select);
        } elseif ($column_name instanceof Field) {
            if (!$column_name->inTableStack($this)) {
                throw new \Exception(t('Field object referenced different table object'));
                return false;
            }
            $field = $column_name;
            $field->showInSelect($show_in_select);
        } elseif ($column_name instanceof Expression || $column_name instanceof SubSelect) {
            $field = $column_name;
        } else {
            throw new \Exception(t('Improper parameter'));
        }
        $this->fields[] = $field;

        return $field;
    }

    /**
     * Returns a Field object. If the column is NOT in the table, a
     * Error is thrown by the Field constructor
     * @param string $column_name Name of the column in this table
     * @param string $alias       Query alias used when referencing this field
     * @return Field
     */
    public function getField($column_name, $alias = null)
    {
        if (!$this->db->allowed($column_name)) {
            throw new \Exception(t('Improper column name "%s"', $column_name));
        }
        $field = new Field($this, $column_name, $alias);
        if (!($field->allowSplat() && $column_name == '*') && (DATABASE_CHECK_COLUMNS && !$this->columnExists($column_name))) {
            throw new \Exception(t('Column does not exist in %s "%s"',
                    get_class($this), $this->getFullName()));
        }
        return $field;
    }

    /**
     * Returns a Conditional object based on a field in the current table.
     *
     * @param string $field_name Column to compare against
     * @param string $value Value to compare column against
     * @param string $operator If NULL, is set to equal (=)
     * @return \Database\Conditional
     */
    public function getFieldConditional($field_name, $value, $operator = null)
    {
        if ($operator == null) {
            $operator = '=';
        }
        if (is_string($field_name)) {
            $field_name = $this->getField($field_name);
        }
        $cond = new Conditional($this->db, $field_name, $value,
                $operator);
        return $cond;
    }

    /**
     * Calls getFieldCondtional and uses it within a DB::addConditional call. Note
     * that addConditional ONLY USES "AND" COMPARISONS. This is just a shortcut.
     * More intricate conditionals should not use this method.
     *
     * @see \Database\Table::getFieldConditional
     * @param string $field_name
     * @param string $value
     * @param string $operator
     */
    public function addFieldConditional($field_name, $value, $operator = null)
    {
        $this->db->addConditional($this->getFieldConditional($field_name,
                        $value, $operator));
    }

    /**
     * If no fields are set, splat is returned
     * @return null|string Contents of the fields in this resource; null if no
     *         fields are present
     */
    public function fieldsAsString()
    {
        if (empty($this->fields)) {
            if ($this->use_in_query) {
                if ($this->alias) {
                    return $this->getAlias() . '.*';
                } else {
                    return $this->getFullName() . '.*';
                }
            }
        } else {
            foreach ($this->fields as $field) {
                $cols[] = $field->stringAsField();
            }
            if (isset($cols)) {
                return implode(', ', $cols);
            }
        }
        return null;
    }

    /**
     * Nulls out the object's fields parameter.
     */
    public function resetFields()
    {
        $this->fields = array();
    }

}

?>
