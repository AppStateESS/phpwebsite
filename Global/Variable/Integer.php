<?php

namespace Variable;

/**
 * A Variable extention for integers.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Integer extends \Variable {

    /**
     * By default, integer variables cannot be null
     * @var boolean
     */
    protected $null_allowed = false;

    /**
     * The lowest integer the value may be. Default to zero but can be
     * -2147483648;
     * @var integer
     */
    private $low_range = 0;

    /**
     * Highest range of integer. PHP does not support unsigned integers.
     * @var integer
     */
    private $high_range = PHP_INT_MAX;
    private $increment = 1;
    private $auto_increment = false;
    protected $column_type = 'Int';

    /**
     * Throws Error exception if value is not an integer, true otherwise
     * @param integer $value
     * @return boolean | \Error
     */
    protected function verifyValue($value)
    {
        if (!is_int($value) && !ctype_digit($value)) {
            throw new \Exception(t('Value "%s" is not an integer', $value));
        }

        if ($value < $this->low_range || $value > $this->high_range) {
            throw new \Exception(t('%s is outside the allowed range(%s - %s)', $this->getLabel(), $this->low_range, $this->high_range));
        }

        return true;
    }

    public function set($value)
    {
        return parent::set((int)$value);
    }


    /**
     * Sets the high and low limits that the value must be between. The increment
     * may be set here as well.
     * @param integer $low_range
     * @param integer $high_range
     * @param integer $increment The amount to multiply by if a range is requested
     */
    public function setRange($low_range = 0, $high_range = 2147483647, $increment = 1)
    {
        $low_range = (int) $low_range;
        $high_range = (int) $high_range;

        if ($low_range > $high_range) {
            throw new \Exception(t('Low range (%s) is greater than high range (%s)', $low_range, $high_range));
        }

        $this->low_range = (int) $low_range;
        $this->high_range = (int) $high_range;
        $this->setIncrement($increment);
    }

    /**
     * The increment used to create a range array.
     * @see Variable\Integer::getRangeArray()
     * @param integer $increment
     */
    public function setIncrement($increment = 1)
    {
        $increment = (int) $increment;
        if ($increment == 0) {
            throw new \Exception(t('Increment cannot be zero'));
        }
        $this->increment = $increment;
    }

    /**
     * Returns the number of times the increment divides into the scale
     */
    public function getDivisions()
    {
        return ceil(($this->high_range - $this->low_range) / $this->increment);
    }

    /**
     * Returns an array of numbers between the low_range and high_range and
     * incremented by its property. Key and value will match.
     * @return array
     */
    public function getRangeArray()
    {
        $range = range($this->low_range, $this->high_range, $this->increment);
        return array_combine($range, $range);
    }

    /**
     * Returns the integer as a string.
     * @return string
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * Returns the value as a php declaration.
     * @return string
     */
    public function getPHP()
    {
        if (is_null($this->value)) {
            return sprintf("\$%s = NULL;", $this->varname, $this->value);
        } else {
            return sprintf("\$%s = %s;", $this->varname, $this->value);
        }
    }

    /**
     * Decreases the value property by current increment value
     * @param integer $amount
     */
    public function decrease()
    {
        $this->value = - $this->increment;
    }

    /**
     * Increases the value property by current increment value
     * @param integer $amount
     */
    public function increase()
    {
        $this->value += $this->increment;
    }

}

?>