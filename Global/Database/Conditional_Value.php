<?php

namespace Database;

/**
 * Holds the value a conditional compares against.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Conditional_Value extends \Data {

    /**
     * The reference can be in several formats: array, string, object.
     * Each are handled differently when requested for a query.
     * @var mixed
     */
    private $reference = null;

    /**
     * Reference to the conditional object that created the value
     * @var Conditional
     */
    private $conditional = null;

    /**
     * @see \Database\Conditional_Value::$reference
     * @param mixed $reference
     * @param Conditional $conditional
     */
    public function __construct($reference, Conditional $conditional)
    {
        $this->conditional = $conditional;
        $this->setReference($reference);
    }

    /**
     * Sets the reference for the Conditional_Value. Quotes the $reference
     * @param mixed $reference
     */
    public function setReference($reference)
    {
        if (!($reference instanceof Expression) && !($reference instanceof Field) && is_string_like($reference)) {
            $this->reference = $this->quote($reference);
        } else {
            $this->reference = $reference;
        }
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
        switch (gettype($reference)) {
            case 'NULL':
                return 'NULL';

            case 'object':
                if ($reference instanceof SubSelect) {
                    return "($reference)";
                } else {
                    return $reference;
                }

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
                return (string) $reference;
        }
    }

    /**
     * Returns the completed value string for a SQL query.
     * @return string
     */
    public function __toString()
    {
        return $this->formatReference($this->reference);
    }

    /**
     * An easy access point for the DB quote function.
     * @see \DB::quote
     * @param mixed $value
     * @return mixed
     */
    private function quote($value)
    {
        return $this->conditional->resource->db->quote($value);
    }

}

?>