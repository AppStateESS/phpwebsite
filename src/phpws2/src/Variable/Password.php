<?php
namespace phpws2\Variable;
/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * @author matt
 */
class Password extends String {

    private $salt;

    public function __construct($value = null, $varname = null, $salt=null)
    {
        parent::__construct($value, $varname);
        $this->salt = $salt;
    }

    public function __toString()
    {
        return $this->get();
    }

    public function get()
    {
        return hash('sha256', $this->salt . $this->value);
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }
    
    public function getSalt()
    {
        return $this->salt;
    }

}
