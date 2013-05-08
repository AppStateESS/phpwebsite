<?php
namespace Variable;
/**
 * A class to assist with float variables.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Float extends \Variable {
    /**
     * Checks to see if value is a float.
     * @param float $value
     * @return boolean | \Error
     */
    protected function verifyValue($value)
    {
        if (!is_float($value)) {
            throw new \Exception(t('Value is not a float'));
        }
        return true;
    }
}
?>