<?php

namespace Database;

/**
 * Helps create a join in a database query.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Join {

    /**
     * The left side Field object in the join
     * @var \Database\Field
     */
    public $left = null;
    /**
     * Left side Resource object
     * @var \Database\Resource
     */
    private $left_resource = null;
    /**
     * The right side Field, Table or Resource object in the join
     * @var \Database\Field
     */
    public $right = null;
    /**
     * Right side Resource object
     * @var \Database\Resource
     */
    private $right_resource = null;
    /**
     * Operator of comparison
     * @var string
     */
    private $operator = '=';
    /**
     * Type of join (left or right, inner, outer, cross, natural)
     * @var string
     */
    private $join_type = null;
    /**
     * If true,
     * @var boolean
     */
    private $resource_join = false;

    /**
     * @param mixed $left Left side of comparison
     * @param mixed $right Right side of comparison
     * @param string $type The join type (inner, outer, etc.)
     * @param string $operator
     */
    public function __construct($left, $right, $type=null, $operator=null)
    {
        $left_is_field = $left instanceof Field;
        $right_is_field = $right instanceof Field;
        $left_is_resource = is_subclass_of($left, 'Resource');
        $right_is_resource = is_subclass_of($right, 'Resource');

        if (!( ($left_is_resource || $left_is_field) && ($right_is_resource || $right_is_field) )) {
            throw new \Exception(t('Join parameters must be SubSelect, Field, or Table objects'));
        }

        if (($left_is_field && !$right_is_field) || (!$left_is_field && $right_is_field)) {
            throw new \Exception(t('Both parameters must be Field objects for conditional joins'));
        }

        $type = $type ? $type : 'INNER';
        $operator = $operator ? $operator : '=';

        $this->left = $left;
        $this->right = $right;
        $this->setType($type);

        // only have to check left, can't have field and resource
        if ($left_is_resource) {
            $this->resource_join = true;
            if ($this->join_type != 'CROSS' && !strstr($this->join_type, 'NATURAL')) {
                throw new \Exception(t('Resource joins without conditionals require a CROSS or NATURAL join'));
            }
        }

        if ($operator) {
            $this->setOperator($operator);
        }
        if ($left_is_resource) {
            $this->left_resource = $this->left;
        } else {
            $this->left_resource = $this->left->resource;
        }

        if ($right_is_resource) {
            $this->right_resource = $this->right;
        } else {
            $this->right_resource = $this->right->resource;
        }

        $this->left_resource->setJoined(true);
        $this->right_resource->setJoined(true);
    }

    /**
     * Allows the type of join to be set. The default variable is NULL, which is an inner join.
     * An exception is returned if an unknown type is requested.
     * @param string type : Type of join to perform.
     */
    public function setType($type)
    {
        static $join_types = array('CROSS', 'INNER', 'LEFT OUTER', 'RIGHT OUTER', 'FULL OUTER',
    'NATURAL', 'NATURAL INNER', 'NATURAL LEFT OUTER', 'NATURAL RIGHT OUTER');

        $type = trim(strtoupper($type));

        if ($type == 'LEFT' || $type == 'OUTER') {
            $type = 'LEFT OUTER';
        }

        if ($type == 'RIGHT') {
            $type = 'RIGHT OUTER';
        }

        if ($type == 'NATURAL LEFT' || $type == 'NATURAL OUTER') {
            $type = 'NATURAL LEFT OUTER';
        }

        if ($type == 'NATURAL RIGHT') {
            $type = 'NATURAL RIGHT OUTER';
        }

        if (!in_array($type, $join_types)) {
            throw new \Exception(t('Unknown join type.'));
        }
        $this->join_type = $type;
    }

    /**
     * @return string Current join type
     */
    public function getType()
    {
        return $this->join_type;
    }

    /**
     * Sets the operator type for the join. The default is equals (=).
     * @param string operation : Type of operator to join on.
     */
    public function setOperator($operator)
    {
        $operator = trim($operator);
        if (!DB::isOperator($operator)) {
            throw new \Exception(t('Unknown operator type.'));
        }
        $this->operator = $operator;
    }

    /**
     * Returns the join as a string to use in the db query
     * @param boolean $show_left
     * @return string
     */
    public function getResourceQuery($show_left=true)
    {
        try {
            if ($show_left) {
                $left_side = $this->left_resource->getResourceQuery();
            } else {
                $left_side = null;
            }

            $right_side = $this->right_resource->getResourceQuery();

            $query = sprintf('%s %s JOIN %s', $left_side, $this->join_type, $right_side);

            if (!$this->resource_join) {
                $query .= sprintf(' ON %s %s %s', $this->left->getFullName(), $this->operator, $this->right->getFullName());
            }
        } catch (Error $e) {
            DB::logError($e);
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
        return $query;
    }

}

?>