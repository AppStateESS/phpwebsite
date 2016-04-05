<?php

namespace Variable;

/**
 * Attribute follows the conventions of the W3 standard for "name".
 * @link http://www.w3.org/TR/2000/REC-xml-20001006#NT-Name
 * @package Global
 * @subpackage Variable
 * @subpackage String
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Attribute extends \Variable\String {

    protected $regexp_match = '/^([a-z_:])([\w\.\-:])*$/i';

    public function __construct($value = null, $varname = null)
    {
        parent::__construct($value, $varname);
        $this->setLimit(255);
    }
}

?>
