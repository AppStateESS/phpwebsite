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

class mysql_Conditional extends DB2_Conditional {
    const like = 'LIKE';
    const regexp = 'REGEXP';
    const not_regexp = 'NOT REGEXP';

    /**
     * Adds 'sounds like' to the allowed operators and passed parameters to parent
     * constructor
     *
     * @see DB2_Conditional::__construct
     * @param $column
     * @param $value
     * @param $operator
     * @param $table_ref
     * @return unknown_type
     */
    public function __construct($column, $value, $operator=null, $resource)
    {
        $this->allowed_operators[] = 'SOUNDS LIKE';
        parent::__construct($column, $value, $operator, $resource);
    }

    public function setOperator($operator)
    {
        if (!$this->checkOperator($operator)) {
            throw new PEAR_Exception(dgettext('core', 'Operator not allowed'));
        }
        $this->operator = $operator;
    }
}

?>