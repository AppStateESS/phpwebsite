<?php

namespace Variable;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Bool extends \Variable {

    /**
     * @var boolean
     */
    protected $null_allowed = false;

    /**
     * @var string
     */
    protected $input_type = 'checkbox';

    protected $column_type = 'tinyint';

    /**
     * Checks if value is a boolean
     * @param boolean $value
     * @return boolean
     */
    protected function verifyValue($value)
    {
        return is_bool($value);
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

}

?>