<?php

/**
 * @author Matt McNaney <mcnaney at gmail dot com>
 */

namespace phpws2\Variable;

class Directory extends \phpws2\Variable\String {

    protected $regexp_match = '/^[^|;,!@#$()<>\\"\'`~{}\[\]=+&\^\s\t]+$/i';

    public function set($value)
    {
        if (!preg_match('@/$@', $value)) {
            $value .= '/';
        }
        parent::set($value);
    }

    public function exists()
    {
        return is_dir($this->value);
    }

    public function writable()
    {
        return is_writable($this->value);
    }

}
