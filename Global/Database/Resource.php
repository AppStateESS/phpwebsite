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
     * If true, then a select query will call all rows of this object.
     * @var boolean
     */
    protected $show_all_fields = true;

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
     * @param string $column
     * @param string $direction Either ASC or DESC
     */
    public function addOrderBy($column, $direction = 'ASC')
    {
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
