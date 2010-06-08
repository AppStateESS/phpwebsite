<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

core\Core::initModClass('pulse', 'PulseController.php');
$p = new PulseController();
$p->process($_REQUEST);

?>
