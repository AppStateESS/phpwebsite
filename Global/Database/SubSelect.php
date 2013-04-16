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
    public function __construct(DB $DB, $alias)
    {
        parent::__construct($DB, $alias);
    }

    /**
     * Returns the alias.
     * @return string
     */
    public function __toString()
    {
        return $this->alias;
    }

    /**
     * Returns the entire subselect for query insertion.
     * @return string
     */
    public function getQuery()
    {
        return sprintf('(%s) AS %s', $this->DB->selectQuery(), $this->getAlias());
    }

}

?>