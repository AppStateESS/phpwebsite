<?php

namespace Variable;

/**
 * A string variable designed for just URLs.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Url extends String {

    /**
     * @var string
     */
    protected $input_type = 'url';

    /**
     * Checks the value to ensure it is a proper url.
     * The construct will throw an exception if the string does not pass.
     * @param string $varname
     * @param string $value
     */
    public function __construct($value = null, $varname = null)
    {
        $this->setRegexpMatch('/^((https?:\/\/)|(.\/))?\w([\.\w\-\/&;?\+=~#])+$/i');
        parent::__construct($value, $varname);
    }

}

?>