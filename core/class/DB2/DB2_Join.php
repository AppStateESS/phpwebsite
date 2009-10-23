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
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package DB2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class DB2_Join {
    public  $left = null;
    private $left_resource = null;
    public  $right = null;
    private $right_resource = null;
    private $operator = '=';
    private $join_type = null;
    private $resource_join = false;

    public function __construct($left, $right, $type=null, $operator=null)
    {
        $left_is_field  = is_a($left, 'DB2_Field');
        $right_is_field = is_a($right, 'DB2_Field');
        $left_is_resource  = is_subclass_of($left, 'DB2_Resource');
        $right_is_resource = is_subclass_of($right, 'DB2_Resource');

        if ( !( ($left_is_resource || $left_is_field) && ($right_is_resource || $right_is_field) ) ) {
            throw new PEAR_Exception(dgettext('core', 'Join parameters must be DB2_SubSelect, DB2_Field, or DB2_Table objects'));
        }

        if ( ($left_is_field && !$right_is_field) || (!$left_is_field && $right_is_field) ) {
            throw new PEAR_Exception(dgettext('core', 'Both parameters must be DB2_Field objects for conditional joins'));
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
                throw new PEAR_Exception(dgettext('core', 'Resource joins without conditionals require a CROSS or NATURAL join'));
            }
        }

        $this->setOperator($operator);
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
            throw new PEAR_Exception(dgettext('core', 'Unknown join type.'));
        }
        $this->join_type = $type;
    }

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
        if (!DB2::isOperator($operator)) {
            throw new PEAR_Exception(dgettext('core', 'Unknown operator type.'));
        }
        $this->operator = $operator;
    }

    public function __toString()
    {
        static $show_left = true;
        if ($show_left) {
            $left_side = $this->left_resource->getQuery();
            $show_left = false;
        } else {
            $left_side = null;
        }

        $right_side = $this->right_resource->getQuery();

        $query = sprintf('%s %s JOIN %s', $left_side, $this->join_type, $right_side);

        if (!$this->resource_join) {
            $query .= sprintf(' ON %s %s %s', $this->left, $this->operator, $this->right);
        }
        return $query;
    }

}

?>