<?php

namespace phpws2\Database;

/**
 * Extended by other classes, this class stores a pseudonym for an expression.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Alias extends \Canopy\Data {

    /**
     * Pseudonym used for an expression or query
     * @var string
     */
    protected $alias = null;

    /**
     * @see Database\Alias
     * @param string $alias
     */
    public function setAlias($alias)
    {
        if (!empty($alias)) {
            $this->alias = $alias;
        }
    }

    /**
     * @return string Current alias
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     *
     * @return boolean True if alias is set
     */
    public function hasAlias()
    {
        return !empty($this->alias);
    }

}
