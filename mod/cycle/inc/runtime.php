<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 *
 */

require_once PHPWS_SOURCE_DIR . 'mod/cycle/inc/defines.php';
if (PHPWS_Core::atHome()) {
    PHPWS_Core::initModClass('cycle', 'Cycle.php');
    Layout::add(Cycle::display(), 'cycle', 'cycle');
}
?>
