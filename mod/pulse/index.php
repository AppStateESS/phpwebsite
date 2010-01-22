<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (!Current_User::allow('pulse')) {
    PHPWS_Core::home();
}

PHPWS_Core::initModClass('pulse', 'Pulse.php');
$pulse = new Pulse;
$pulse->admin();

?>
