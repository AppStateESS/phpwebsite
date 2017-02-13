<?php

namespace phpws2\Variable;

/**
 * Description of Decimal
 *
 * @author matt
 */
class DecimalVar extends FloatVar
{

    /**
     * Checks to see if value is a decimal.
     * @param float $value
     * @return boolean | \phpws2\Error
     */
    protected function verifyValue($value)
    {
        // mysql returns strings so "1" fails
        // the below is the work around
        $value = $value + 0.0;
        if (!is_float($value)) {
            throw new \Exception(sprintf('Value is not a decimal. Type:' . gettype($value)));
        }
        return true;
    }

}
