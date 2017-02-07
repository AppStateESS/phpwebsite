<?php

namespace phpws2\Variable;

/**
 * String variable that is numbers only.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class NumberString extends \phpws2\Variable\StringVar
{
    protected $regexp_match = '/^-?[\d\.]+$/i';

}

