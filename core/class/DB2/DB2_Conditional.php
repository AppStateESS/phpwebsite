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
 * This is a conditional class for use in limiting update, delete, or select
 * queries. It is used in both where and having clauses. This class is abstract
 * and uses its factory method to create a database OS specific object.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Conditional_Value.php';

abstract class DB2_Conditional extends Data {
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
     * Determines whether the conjunction should be shown or not when the object is printed
     * @var boolean
     */
    private $show_conj = true;

    /**
     * Array of allowed operators for testing in setOperator.
     * @var array
     */
    protected $allowed_operators = array('=', '>', '<', '>=', '<=', '<>', '!=', '<=>', 'LIKE', 'ILIKE',
'NOT LIKE', 'NOT ILIKE','REGEXP', 'RLIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
'IS', 'IS NOT', '~');

    /**
     * This object's index in the table's where stack.
     * @var integer
     * @access public
     */
    public $where_stack_number = 0;

    public function dropFromTableStack()
    {
        $this->resource->dropFromWhereStack($this->stack_number);
    }

    /**
     * The factory that extend this class should parse to be sure the operators fit the db OS.
     * For example, 'REGEXP' is unknown to Postgresql. pgsql needs to insure it is used properly
     * regardless.
     * @param string $operator
     * @return void
     */
    abstract protected function setOperator($operator);

    /**
     * Creates conditional object
     * @param string|DB2_Field $column
     * @param string|DB2_Conditional_Value $value
     * @param string|DB2_Expression $operator
     * @param DB2_Table|DB2_SubSelect $resource
     * @return void
     */
    public function __construct($column, $value, $operator=null, $resource)
    {
        $this->resource = $resource;
        $this->setColumn($column);
        $this->setValue($value);
        if ($operator && (is_string($operator) || is_a($operator, 'DB2_Expression'))) {
            $this->setOperator($operator);
        } else {
            $this->setOperator('=');
        }
    }

    public function getOperator()
    {
        return strtoupper($this->operator);
    }

    public function setConjunction($conjunction)
    {
        $conjunction = strtoupper(trim($conjunction));
        if ($conjunction != 'AND' && $conjunction != 'OR') {
            throw new PEAR_Exception(dgettext('core', 'Conditional conjunction must be AND or OR only'));
        }
        $this->conjunction = $conjunction;
    }

    public function getConjunction()
    {
        return $this->conjunction;
    }

    /**
     * Conjunction is done in getConditional or addConditional
     * @param string $column
     * @param unknown_type $value
     * @param string $operator
     * @param DB2_Table|DB2 $resource
     * @return unknown_type
     */
    static public function factory($column, $value, $operator=null, $resource)
    {
        $dbtype = $resource->getDBType();
        $file = PHPWS_SOURCE_DIR . "core/class/DB2/factory/{$dbtype}_Conditional.php";
        if (!is_file($file)) {
            throw new PEAR_Exception(dgettext('core', 'Conditional functionality is not available for this database type'));
        }
        require_once $file;
        $class_name = $dbtype . '_Conditional';
        return new $class_name($column, $value, $operator, $resource);
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
                    throw new PEAR_Exception(dgettext('core', 'Table name included with the column name differs from the current table object'));
                } else {
                    $column = str_replace($this->resource . '.', $column);
                }
            }
            if ($this->resource->verifyColumn($column)) {
                $this->column = $column;
            } else {
                throw new PEAR_Exception(dgettext('core', 'Unknown column'));
            }
        } elseif (is_object($column) && (is_a($column, 'DB2_Field') || is_a($column, 'DB2_Expression')) ) {
            $this->column = $column;
        } else {
            throw new PEAR_Exception(dgettext('core', 'Conditional column must be a string, DB2_Field, or DB2_Expression object'));
        }
    }

    public function getColumn()
    {
        if (is_string($this->column)) {
            return $this->resource->__toString() . '.' . $this->column;
        } else {
            return $this->column;
        }
    }

    public function setValue($value)
    {
        if (is_a($value, 'DB2_Conditional_Value')) {
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
                        throw new PEAR_Exception(dgettext('core', 'Null value incompatible with operator'));
                }
            }
            $this->value = new DB2_Conditional_Value($value, $this);
        }
        return $this->value;
    }

    public function disableConjunction()
    {
        $this->show_conj = false;
    }

    public function checkOperator($operator)
    {
        if (is_a($operator, 'DB2_Expression')) {
            return true;
        }
        return in_array(strtoupper($operator), $this->allowed_operators);
    }

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