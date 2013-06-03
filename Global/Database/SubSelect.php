<?php

namespace Database;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class SubSelect extends Resource {

    /**
     * Constructs the SubSelect object. Unlike Resource, alias as is required.
     * @param $DB
     * @param $alias
     * @return unknown_type
     */
    public function __construct(DB $DB, $alias=null)
    {
        parent::__construct($DB, $alias);
    }

    /**
     * Returns the alias.
     * @return string
     */
    public function __toString()
    {
        return '(' . $this->db->selectQuery() . ')';
    }

    /**
     * Returns the entire subselect for query insertion.
     * @return string
     */
    public function getResourceQuery()
    {
        if (!$this->hasAlias()) {
            throw new \Exception('Subselect may not be used as a resource without an alias');
        }
        return sprintf('%s AS %s', $this->__toString(), $this->getAlias());
    }

}

?>