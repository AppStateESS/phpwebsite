<?php

namespace phpws2\Variable;

/**
 * String variable that is alphanumeric with underlines only. Value must begin
 * with a letter.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Alphanumeric extends \phpws2\Variable\CanopyString {

    protected $regexp_match = '/^[a-z]\w*$/i';

}

