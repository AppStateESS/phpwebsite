<?php

namespace phpws2\Variable;

/**
 * Description of Decimal
 *
 * @author matt
 */
class Decimal extends Float
{

    /**
     * Checks to see if value is a decimal.
     * @param float $value
     * @return boolean | \Error
     */
    protected function verifyValue($value)
    {
        if (!is_float($value)) {
            throw new \Exception(t('Value is not a decimal. Type:' . gettype($value)));
        }
        return true;
    }

}
