<?php

/**
 * Crutch display of old modules
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
if (isset($GLOBALS['pre094_modules'])) {
    PHPWS_Crutch::getOldLayout();
 }

echo Layout::display();

?>