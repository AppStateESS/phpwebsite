<?php

/**
 * Crutch display of old modules
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (Current_User::allow('layout')) {
    Layout::miniLinks();
 }

Layout::showKeyStyle();

echo Layout::display();
?>