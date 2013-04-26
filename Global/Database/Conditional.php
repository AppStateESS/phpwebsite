<?php

namespace Database;

/**
 * This is a conditional class for use in limiting update, delete, or select
 * queries. It is used in both where and having clauses.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Conditional extends \Data {

    /**
     * Reference to the resource spawning this object
     * @var object
     * @access protected
     */
    public $resource = null;

    /**
     * Column compared to the comparison value.
     * Usually this is a column name
     * @var string
     * @access private
     */
    private $column = null;

    /**
     * Value compared to the column variable by the operator
     * @var string
     * @access private
     */
    private $value = null;

    /**
     * The operator used to compare the left to right fields
     * @var string
     * @access protected
     */
    protected $operator = '=';

    /**
     * Conjunction to the previous where condition
     * Will be either "and" or "or".
     * @var string
     * @access private
     */
    private $conjunction = 'AND';

    /**
     * Determines whether the conjunction should be shown or not when the object
     * is returned as a string
     * @var boolean
     */
    private $show_conj = true;

    /**
     * Array of allowed operators for testing in setOperator.
     * @var array
     */
    protected $allowed_operators = array('=', '>', '<', '>=', '<=', '<>', '!=', '<=>', 'LIKE', 'ILIKE',
        'NOT LIKE', 'NOT ILIKE', 'REGEXP', 'RLIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
        'IS', 'IS NOT', '~');

    /**
     * This object's index in the table's where stack.
     * @var integer
     * @access public
     */
    public $stack_number = 0;

    /**
     * Removes this conditional from the table stack it is associated with.
     */
    public function dropFromTableStack()
    {
        $this->resource->dropFromWhereStack($this->stack_number);
    }

    /*
     * @todo was abstract, now needs fleshing out
     * @param string $operator
     */
    protected function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * Creates conditional object
     * @param \Database\Resource $resource
     * @param string|Field $column
     * @param string|Conditional_Value $value
     * @param string|Expression $operator
     */
    public function __construct(\Database\Resource $resource, $column, $value, $operator=null)
    {
        $this->resource = $resource;
        $this->setColumn($column);
        $this->setValue($value);
        if ($operator && (is_string($operator) || $operator instanceof Expression)) {
            $this->setOperator($operator);
        } elseif(is_array($value)) {
            $this->setOperator('in');
        } else {
            $this->setOperator('=');
        }
    }

    /**
     * @return string The operator used in this conditional
     */
    public function getOperator()
    {
        return strtoupper($this->operator);
    }

    /**
     * Allow the setting of the conjuntion to AND or OR.
     * @param string $conjunction
     */
    public function setConjunction($conjunction)
    {
        $conjunction = strtoupper(trim($conjunction));
        if ($conjunction != 'AND' && $conjunction != 'OR') {
            throw new \Exception(t('Conditional conjunction must be AND or OR only'));
        }
        $this->conjunction = $conjunction;
    }

    /**
     * Returns the AND or OR conjunction
     * @return string
     */
    public function getConjunction()
    {
        return $this->conjunction;
    }

    /**
     * Allows the setting of the conditional column.
     * @param $column
     * @return unknown_type
     */
    public function setColumn($column)
    {
        if (is_string($column)) {
            if (strstr($column, '.')) {
                $column_parts = explode('.', $column);
                if ($column_parts['0'] != $this->resource->getName()) {
                    throw new \Exception(t('Table name included with the column name differs from the current table object'));
                } else {
                    $column = str_replace($this->resource . '.', $column);
                }
            }

            if (DATABASE_CHECK_COLUMNS && !$this->resource->columnExists($column)) {
                throw new \Exception(t('Unknown column: "%s"', $column));
            } else {
                $this->column = $column;
            }
        } elseif (is_object($column) && ($column instanceof Field || $column instanceof Expression)) {
            $this->column = $column;
        } else {
            throw new \Exception(t('Conditional column must be a string, Field, or Expression object'));
        }
    }

    /**
     * Returns the column name the conditional is acting on
     * @return string
     */
    public function getColumn()
    {
        if (is_string($this->column)) {
            return $this->resource->__toString() . '.' . $this->column;
        } else {
            return $this->column;
        }
    }

    /**
     * References or creates a Condition_Value object in this object's value
     * parameter.
     * @param mixed $value
     * @return mixed
     */
    public function setValue($value)
    {
        if ($value instanceof Conditional_Value) {
            $this->value = $value;
        } else {
            if (is_null($value)) {
                switch ($this->getOperator()) {
                    case '=':
                    case 'IS':
                        $this->operator = 'IS';
                        break;

                    case 'IS NOT':
                    case '<>':
                    case '!=':
                        $this->operator = 'IS NOT';
                        break;

                    default:
                        throw new \Exception(t('Null value incompatible with operator'));
                }
            }
            $this->value = new Conditional_Value($value, $this);
        }
        return $this->value;
    }

    /**
     * Changes the show_conj parameter to false.
     * @see \Database\Conditional::$show_conj
     */
    public function disableConjunction()
    {
        $this->show_conj = false;
    }

    /**
     * Returns true is the $operator parameter may be used in a conditional
     * comparison.
     * @param Expression|string $operator
     * @return boolean
     */
    public function checkOperator($operator)
    {
        if ($operator instanceof Expression) {
            return true;
        }
        return in_array(strtoupper($operator), $this->allowed_operators);
    }

    /**
     * Returns the value parameter as a string;
     * @return string
     */
    public function getValue()
    {
        return $this->value->__toString();
    }

    /**
     * Returns the where portion of the sql query. If $this->show_conj is
     * false, the conjunction is not prefixed to the output.
     */
    public function __toString()
    {
        if ($this->show_conj) {
            return sprintf('%s %s %s %s', $this->getConjunction(), $this->getColumn(), $this->getOperator(), $this->getValue());
        } else {
            return sprintf('%s %s %s', $this->getColumn(), $this->getOperator(), $this->getValue());
        }
    }

}

?>