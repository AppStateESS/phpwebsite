<?php

namespace Variable;

/**
 * Description of File
 *
 * @author matt
 */
class File extends \Variable\String {

    protected $input_type = 'file';

    public function __construct($value = null, $varname = null)
    {
        $this->setRegexpMatch('/^[^|;,!@#$()<>\\"\'`~{}\[\]=+&\^\s\t]+(\w+(\.\w+))$/i');
        parent::__construct($value, $varname);
    }

    public function exists()
    {
        return is_file($this->value);
    }

    public function writable()
    {
        return is_writable($this->value);
    }

    public function requireOnce()
    {
        if (!$this->exists()) {
            throw new \Exception(t('File not found: %s', $this->__toString()));
        }

        require_once $this->value;
    }

}

?>
