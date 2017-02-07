<?php

namespace phpws2\Variable;

if (!defined('NUMBER_STRING_DIVIDER')) {
    define('NUMBER_STRING_DIVIDER', '-');
}

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PhoneNumber extends \phpws2\Variable\NumberString
{
    private $divider = NUMBER_STRING_DIVIDER;
    private $format_number = false;
    protected $limit = 30;

    /**
     * Strips extra characters
     * @param string $value
     */
    public function set($value)
    {
        $value = preg_replace('/[^\d]/', '', $value);
        parent::set($value);
    }

    public function get()
    {
        return $this->__toString();
    }

    public function formatNumber($format)
    {
        $this->format_number = (bool) $format;
    }

    /**
     * @deprecated
     * @param type $divider
     * @throws Exception
     */
    public function setDivider($divider)
    {
        // a null may be used as a divider
        if (strlen($divider) > 1) {
            throw new \Exception('Divider may not be great than a single character');
        }

        $this->divider = $divider;
    }

    public function __toString()
    {
        $value = parent::__toString();
        if ($this->format_number) {
            $value = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $value);
        }
        return $value;
    }

}
