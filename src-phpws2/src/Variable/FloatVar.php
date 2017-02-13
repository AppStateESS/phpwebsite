<?php

namespace phpws2\Variable;

/**
 * A class to assist with float variables.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class FloatVar extends \phpws2\Variable
{
    /**
    /* We use decimal instead of float as it is used
    /* in mysql and pgsql.
     * If you must use float, overwrite the column_type
     * @var string
     */
    protected $column_type = 'decimal';

    /**
     * Checks to see if value is a float.
     * @param float $value
     * @return boolean | \phpws2\Error
     */
    protected function verifyValue($value)
    {
        if (!is_float($value)) {
            throw new \Exception('Value is not a float');
        }
        return true;
    }

    /**
     * Returns the float as a string.
     * @return string
     */
    public function __toString()
    {
        return (string) $this->get();
    }

}
