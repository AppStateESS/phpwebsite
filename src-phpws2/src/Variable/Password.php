<?php
namespace phpws2\Variable;
/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * @deprecated Don't use this. Use password_hash with StringVar
 * @author matt
 */
class Password extends StringVar {

    private $salt;
    
    /**
     * If set to true, the set password will be hashed. Otherwise, it will
     * be returned as set. This is helpful when a password is not updated
     * and you don't want to hash the hash.
     * @var boolean
     */
    private $hash_result = true;

    public function __construct($value = null, $varname = null, $salt=null)
    {
        parent::__construct($value, $varname);
        $this->salt = $salt;
    }
    
    public function __toString()
    {
        return $this->get();
    }

    
    public function setHashResult($val)
    {
        $this->hash_result = (bool) $val;
    }
    
    public function get()
    {
        if ($this->hash_result) {
            return hash('sha256', $this->salt . $this->value);
        } else {
            return $this->value;
        }
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
