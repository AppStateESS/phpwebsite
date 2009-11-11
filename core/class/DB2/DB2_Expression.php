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
 * DB2_Expression allows the developer freedom to use any operator or function available.
 * It can be very helpful (or very harmful) depending on its use.
 * Developers need to be very sure that any user input inserted into an
 * Expression object has been parsed. DB2 does not quote expressions. It also does not
 * check the validity of tables, columns, or database specific function or operator calls.
 * What it does do, is forego standard checks to allow any type of expression in the
 * query.
 *
 * Example:
 * $db2 = new DB2;
 * $db2->addTable('foo');
 * $expression = new DB2_Expression('sounds like'); //mysql specific operator
 * $db2->addWhere('some_value', 'orange', $expression);
 * $db2->select();
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class DB2_Expression extends DB2_Alias {
    /**
     * String containing the expression
     * @var string
     */
    public $expression = null;

    public function __construct($expression, $alias=null)
    {
        $this->expression = $expression;
        $this->setAlias($alias);
    }

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