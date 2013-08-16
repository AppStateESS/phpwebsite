<?php

namespace Variable;

/**
 * String variable that is numbers only.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class NumberString extends \Variable\String {

    protected $regexp_match = '/^\d+$/i';

}

?>
