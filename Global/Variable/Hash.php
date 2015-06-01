<?php

namespace Variable;

/**
 * Contains an alphanumeric hash. No spaces or other characters.
 * 
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class Hash extends String
{
      protected $regexp_match = '/^\w+$/';
}
