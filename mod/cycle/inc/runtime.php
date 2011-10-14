<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 *
 */
if (PHPWS_Core::atHome()) {
    PHPWS_Core::initModClass('cycle', 'Cycle.php');
    Layout::add(Cycle::display());

    //Layout::add(javascriptMod('cycle', 'cycle'));
}
?>
