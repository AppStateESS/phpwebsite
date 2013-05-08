<?php
namespace Variable;
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Color extends String {
    protected $input_type = 'color';

    public function __construct($value, $varname=null)
    {
        $this->setLimit('6');
        $value = strtolower($value);
        $this->setRegexpMatch('/^([a-f]|[0-9]){6}$/');
        parent::__construct($value, $varname);
    }

    public function getInput()
    {
        $input = parent::getInput();
        $input->setSize(6,6);
        return $input;
    }
}
?>