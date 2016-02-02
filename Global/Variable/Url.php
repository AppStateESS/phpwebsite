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
     * This brain-frying regular expression was written by Diego Perini @ https://gist.github.com/dperini/729294
     * @var string
     */
    protected $regexp_match = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS';
    
    /**
     * Checks the value to ensure it is a proper url.
     * The construct will throw an exception if the string does not pass.
     * @param string $varname
     * @param string $value
     */
    public function __construct($value = null, $varname = null)
    {
        parent::__construct($value, $varname);
    }

}

?>