<?php

/**
 * Crutch display of old modules
 */
if (isset($GLOBALS['pre094_modules']))
  PHPWS_Crutch::getOldLayout();

echo Layout::display();

?>