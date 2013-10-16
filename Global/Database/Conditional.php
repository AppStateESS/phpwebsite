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
     * Left side of the conditional
     * @var mixed
     */
    protected $left;

    /**
     * Right side of the conditional
     * @var mixed
     */
    protected $right;

    /**
     * The operator used to compare the left to right fields
     * @var string
     * @access protected
     */
    protected $operator = '=';

    private $db;

    /**
     * Array of allowed operators for testing in setOperator.
     * @var array
     */
    private static $allowed_operators = array('=', '>', '<', '>=', '<=', '<>', '!=', '<=>', 'LIKE', 'ILIKE',
        'NOT LIKE', 'NOT ILIKE', 'REGEXP', 'RLIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
        'IS', 'IS NOT', '~', 'AND', 'OR');

    public function __construct(\Database\DB $db, $left, $right, $operator)
    {
        $this->db = $db;
        $this->setLeft($left);
        $this->setRight($right);
        $this->setOperator($operator);
    }

    /**
     * @param string $operator
     */
    protected function setOperator($operator)
    {
        $operator = strtoupper($operator);
        if (!in_array($operator, self::$allowed_operators)) {
            throw new \Exception(t('Unknown operator'));
        }
        $this->operator = $operator;
    }

    private function testSide($side)
    {
        if (is_object($side) && !is_string_like($side)) {
            throw new \Exception('Conditional variable received a variable type it can not use');
        }
    }

    public function setLeft($left)
    {
        if ($left instanceof \Database\SubSelect) {
            throw new \Exception('Left side conditional may not be a SubSelect object');
        }
        $this->testSide($left);
        $this->left = $left;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function getRight()
    {
        return $this->quoteValue($this->right);
    }

    private function quoteValue($value)
    {
        switch (gettype($value)) {
            case 'integer':
                return $value;

            case 'object':
                if (method_exists($value, 'stringAsConditional')) {
                    return $value->stringAsConditional();
                } elseif (is_string_like($value)) {
                    return $this->db->quote($value->__toString());
                } else {
                    throw new \Exception(t('Could not use object variable in a query comparison'));
                }

            case 'NULL':
                return 'NULL';

            case 'array':
                $value = \Database\DB::quote($value);
                return '(' . implode(',', $value) . ')';

            default:
                return \Database\DB::quote($value);
        }
    }

    public function setRight($right)
    {
        $this->testSide($right);
        $this->right = $right;
    }

    /**
     * @return string The operator used in this conditional
     */
    public function getOperator()
    {
        return $this->operator;
    }

    public function stringAsConditional()
    {
        return $this->__toString();
    }

    /**
     * Returns the where portion of the sql query. If $this->show_conj is
     * false, the conjunction is not prefixed to the output.
     */
    public function __toString()
    {
        return '(' . $this->getLeft() . ' ' . $this->getOperator() . ' ' . $this->getRight() . ')';
    }

}

?>