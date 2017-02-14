<?php

namespace phpws2\Variable;

/**
 * Contains an alphanumeric hash. No spaces or other characters.
 * 
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class HashVar extends StringVar
{
      protected $regexp_match = '/^\w+$/';
      
      public function createRandom($length=32, $confusables=false, $uppercase=false)
      {
          $this->value = \Canopy\TextString::randomString($length, $confusables, $uppercase);
      }
      
      public function md5Random()
      {
          $this->createRandom();
          $this->value = md5($this->value);
      }
}
