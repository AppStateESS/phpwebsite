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

class DB2_Conditional_Value extends Data {
    /**
     * The reference can be in several formats: array, string, object.
     * Each are handled differently when requested for a query.
     * @var mixed
     */
    private $reference = null;

    /**
     * Reference to the conditional object that created the value
     * @var DB2_Conditional
     */
    private $conditional = null;

    public function __construct($reference, DB2_Conditional $conditional)
    {
        $this->conditional = $conditional;
        $this->setReference($reference);
    }

    public function setReference($reference)
    {
        $this->reference = $this->quote($reference);
    }

    /**
     * Formats the reference value into a string. This function depends on setReference
     * properly quoting its value.
     *
     * @param mixed $reference
     * @return string
     */
    private function formatReference($reference)
    {
        switch(gettype($reference)) {
            case 'NULL':
                return 'NULL';

            case 'object':
                return  "($reference)";

            case 'array':
                if ($this->conditional->getOperator() == 'BETWEEN') {
                    $query = current($reference);
                    $query .= ' AND ';
                    $query .= next($reference);
                    return "($query)";
                }

                foreach ($reference as $array_value) {
                    $query_string[] = $this->formatReference($array_value);
                }
                return '(' . implode(', ', $query_string) . ')';

            default:
                return (string)$reference;
        }
    }

    public function __toString()
    {
        return $this->formatReference($this->reference);
    }

    /**
     * An easy access point for the DB2 quote function.
     * @see DB2::quote
     * @param mixed $value
     * @return mixed
     */
    private function quote($value)
    {
        return $this->conditional->resource->db2->quote($value);
    }
}
?>