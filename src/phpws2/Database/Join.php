<?php

namespace Database;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Join {

    private $left;
    private $right;
    private $conditional;
    private $join_type = 'INNER';

    public function __construct(\Database\Resource $left_resource, \Database\Resource $right_resource, $join_type = null, $conditional = null)
    {
        $this->setLeft($left_resource);
        $this->setRight($right_resource);
        if (!empty($join_type)) {
            $this->setJoinType($join_type);
        }

        if (!empty($conditional)) {
            $this->setConditional($conditional);
        }
    }

    public function setConditional(Conditional $conditional)
    {
        $this->conditional = $conditional;
    }

    /**
     * Allows the type of join to be set. The default variable is NULL, which is an inner join.
     * An exception is returned if an unknown type is requested.
     * @param string type : Type of join to perform.
     */
    public function setJoinType($type)
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
            throw new \Exception(t('Unknown join type: %s', $type));
        }
        $this->join_type = $type;
    }

    public function setLeft($resource)
    {
        if (!is_subclass_of($resource, '\Database\Resource')) {
            throw new \Exception(t('Resource object required'));
        }

        $resource->setJoined(true);
        $this->left = $resource;
    }

    public function setRight($resource)
    {
        if (!is_subclass_of($resource, '\Database\Resource')) {
            throw new \Exception(t('Join parameters must be SubSelect, Field, or Table objects'));
        }
        $resource->setJoined(true);
        $this->right = $resource;
    }

    public function getResourceQuery($show_left = true)
    {
        if ($show_left) {
            $left_side = $this->left->getResourceQuery();
        } else {
            $left_side = null;
        }

        $right_side = $this->right->getResourceQuery();
        $query = sprintf('%s %s JOIN %s', $left_side, $this->join_type,
                $right_side);

        if (!empty($this->conditional)) {
            $query .= ' ON ' . $this->conditional->__toString();
        } else {
            if ($this->join_type != 'CROSS' && !strstr($this->join_type,
                            'NATURAL')) {
                throw new \Exception(t('Joins without conditionals require a CROSS or NATURAL join'));
            }
        }
        return $query;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function getRight()
    {
        return $this->right;
    }

}

?>
