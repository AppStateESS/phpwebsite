<?php

namespace phpws2\Variable;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Bool extends \phpws2\Variable
{

    /**
     * @var boolean
     */
    protected $null_allowed = false;

    /**
     * @var string
     */
    protected $input_type = 'checkbox';
    protected $column_type = 'boolean';

    /**
     * Checks if value is a boolean
     * This method is NOT used by set(). See below.
     * @param boolean $value
     * @return boolean
     */
    protected function verifyValue($value)
    {
        if ($this->isTrue($value) || $this->isFalse($value)) {
            return true;
        } else {
            throw new \Exception('Value is not boolean');
        }
    }

    public function defineAsPHP()
    {
        $val = $this->value ? 'true' : 'false';
        return "\${$this->varname} = $val;";
    }

    public function defineAsJavascriptParameter()
    {
        $val = $this->value ? 'true' : 'false';
        return "'{$this->varname}' : $val";
    }

    public function defineAsJavascriptVar()
    {
        $val = $this->value ? 'true' : 'false';
        return "var {$this->varname} : $val;";
    }

    /**
     * For database result, we are returning a 1 for true or a 0 for false. This
     * is because the default column type for a Bool object is a smallint
     *
     * @return integer
     */
    public function toDatabase()
    {
        return $this->value ? 1 : 0;
    }

    public function __toString()
    {
        return $this->value ? '1' : '0';
    }

    private function isTrue($value)
    {
        static $truism = array('1', 1, 'true', true, 'yes');

        return in_array($value, $truism, true);
    }

    private function isFalse($value)
    {
        static $falsehood = array('0', 0, 'false', false, 'no');

        return in_array($value, $falsehood, true);
    }

    /**
     * Because of the interesting way php defines booleans,
     * the parent set method needed to be overwritten.
     * 
     * @param mixed $value
     * @return boolean
     */
    public function set($value)
    {
        if ($this->isTrue($value)) {
            return parent::set(true);
        } elseif ($this->isFalse($value)) {
            return parent::set(false);
        } else {
            throw new \Exception('Value is not boolean');
        }
    }

}
