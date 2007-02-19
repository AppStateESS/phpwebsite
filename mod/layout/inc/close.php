<?php

/**
 * Crutch display of old modules
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

translate('layout');
if (Current_User::allow('layout')) {
    Layout::miniLinks();
 }

Layout::showKeyStyle();

echo Layout::display();
translate();
?>