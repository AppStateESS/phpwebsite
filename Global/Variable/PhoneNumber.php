<?php
namespace Variable;

if (!defined('NUMBER_STRING_DIVIDER')) {
    define('NUMBER_STRING_DIVIDER', '-');
}

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PhoneNumber extends \Variable\NumberString 
{
    private $divider = NUMBER_STRING_DIVIDER;
    
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
        $value = parent::get();
    }
    
    public function setDivider($divider)
    {
        // a null may be used as a divider
        if (strlen($divider) > 1) {
            throw new Exception('Divider may not be great than a single character');
        }
        
        if (!preg_match('/[\.\-\s]/', $divider)) {
            
        }
        $this->divider = $divider;
    }
}
