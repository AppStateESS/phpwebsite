<?php

namespace Database;

/**
 * Expression allows the developer freedom to use any operator or function available.
 * It can be very helpful (or very harmful) depending on its use.
 * Developers need to be very sure that any user input inserted into an
 * Expression object has been parsed. DB does not quote expressions. It also does not
 * check the validity of tables, columns, or database specific function or operator calls.
 * What it does do, is forego standard checks to allow any type of expression in the
 * query.
 *
 * Example:
 * $DB = \Database::newDB()();
 * $foo = $DB->addTable('foo');
 * $expression = $DB->getExpression('sounds like'); //mysql specific operator
 * $foo->addWhere('some_value', 'orange', $expression);
 * $DB->select();
 *
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Expression extends Alias {

    /**
     * String containing the expression
     * @var string
     */
    public $expression = null;

    /**
     * @param string $expression
     * @param string $alias
     */
    public function __construct($expression, $alias=null)
    {
        $this->expression = $expression;
        $this->setAlias($alias);
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->alias)) {
            return $this->expression;
        } else {
            return "({$this->expression}) AS " . $this->getAlias();
        }
    }

}

?>