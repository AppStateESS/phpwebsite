<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if ($_SESSION['User']->isLogged()) {
    Layout::add(PHPWS_ControlPanel::display());
}
?>