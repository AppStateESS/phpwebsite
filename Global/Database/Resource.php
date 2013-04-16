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
     * @var object
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
     * If true, then a select query will call all rows of this object.
     * @var boolean
     */
    protected $show_all_fields = true;

    /**
     * An array of conditional objects. This array will be emptied as conditional objects
     * are placed into the DB object
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

    /**
     * @return string A string representing the contents of this resource
     */
    abstract public function getQuery();

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
     * @return null|string Contents of the fields in this resource; null if no
     *         fields are present
     */
    public function getFields()
    {
        if (empty($this->fields)) {
            if ($this->show_all_fields) {
                if ($this->alias) {
                    return $this->getAlias() . '.*';
                } else {
                    return wrap($this->full_name, $this->db->getDelimiter()) . '.*';
                }
            }
        } else {
            // previously, I was checking the validity of the field object here.
            // I am assuming at this point that it is allowed in the fields array
            // it is ok to use __toString on it. Forcing a field or subselect check
            // made expressions fail.
            foreach ($this->fields as $field) {
                $cols[] = $field;
            }
            if (isset($cols)) {
                return implode(', ', $cols);
            }
        }
        return null;
    }

    /**
     * Adds a field object to the table object's field stack.
     * @param mixed $column_name    If not a Field object, then the name of the column in the table or Expresssion
     * @param string           $alias          An alias to be used within the query for this field.
     * @param boolean          $show_in_select If true, show in a select query. False, otherwise.
     * @return Field
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
            //$field = $this->getField($column_name, $alias, $this);
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
            throw new \Exception(t('Column does not exist in %s "%s"', get_class($this), $this->getFullName()));
        }
        return $field;
    }

    /**
     * Receives an associative array of column=>value pairings and returns
     * an array of Conditional where objects.
     * @param array $where_array
     * @return array
     */
    public function addWhereArray(array $where_array)
    {
        foreach ($where_array as $col => $val) {
            $ret_array[] = $this->addWhere($col, $val);
        }
        return $ret_array;
    }

    /**
     * Creates a new Conditional object into and places it on the where_stack.
     *
     * @param string|object $column : Name of table column or a conditional object
     * @param string $value
     * @param string $operator Comparison operator (e.g. =, >, <, !=)
     * @param string $conjunction Either AND or OR.
     * @return object
     */
    public function addWhere($column, $value, $operator = null, $conjunction = 'AND')
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
                throw new \Exception(t('Conditional object referenced incorrect table object'));
                return false;
            }
            $where = $column;
        } elseif ($column instanceof Expression || $column instanceof SubSelect) {
            $where = $this->getConditional($column->getAlias(), $value, $operator, $conjunction);
        } else {
            $where = $this->getConditional($column, $value, $operator, $conjunction);
        }
        $where->stack_number = $stack_number;
        $this->where_stack[$stack_number] = $where;
        $stack_number++;
        return $where;
    }

    /**
     * Returns the where objects as a string for use in the final query.
     *
     * @param boolean $conjunction If true, the where object from the where stack
     * will use its conjunction. False prevents this. Used to prevent the first
     * conjuction in following the "WHERE" clause in the query.
     * @return string|void
     */
    public function getWhereStack($conjunction = true)
    {
        if (!empty($this->where_stack)) {
            foreach ($this->where_stack as $w) {
                if (!$conjunction) {
                    $w->disableConjunction();
                    $conjunction = true;
                }
                $where_list[] = $w->__toString();
            }
        }
        if (isset($where_list)) {
            return implode(' ', $where_list);
        }
    }

    /**
     * Constructs and returns a conditional object.
     * @param \Database\Column|string $column
     * @param string $value
     * @param string $operator
     * @param string $conjunction
     * @return \Database\Conditional
     */
    public function getConditional($column, $value, $operator = null, $conjunction = null)
    {
        $conditional = new Conditional($this, $column, $value, $operator);
        if (empty($conjunction)) {
                $conjunction = 'AND';
        }
        $conditional->setConjunction($conjunction);
        return $conditional;
    }

    /**
     * Removes a where object from the where_stack property.
     * @param integer $stack_number
     */
    public function dropFromWhereStack($stack_number)
    {
        unset($this->where_stack[$stack_number]);
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
     * If passed a parameter, it sets the show_all_fields variable.
     * Returns variable condition.
     * @param boolean show
     * @return boolean
     */
    public function showAllFields($show = null)
    {
        if (isset($show)) {
            $this->show_all_fields = (bool) $show;
        }
        return $this->show_all_fields;
    }

    /**
     * Set a column to order by and the direction to do so.
     * @param string $column
     * @param string $direction Either ASC or DESC
     */
    public function addOrderBy($column, $direction = 'ASC')
    {
        static $allowed_directions = array('ASC', 'DESC', 'RAND', 'RANDOM');
        $direction = trim(strtoupper($direction));

        if (!in_array($direction, $allowed_directions)) {
            throw new \Exception(t('Unknown order direction'));
        }

        // If a random call, return the db os specific function call.
        if ($direction == 'RAND' || $direction == 'RANDOM') {
            $direction = $this->db->getRandomCall();
        }

        if (DATABASE_CHECK_COLUMNS && !$this->columnExists($column)) {
            throw new \Exception(t('Table column "%s" is not known', $column));
        } else {
            $this->orders[] = $this->__toString() . ".$column $direction";
        }
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
        $this->show_all_fields = true;
        $this->where_stack = null;
    }

}

?>
