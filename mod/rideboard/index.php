<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('rideboard', 'Rideboard.php');
$rideboard = new Rideboard;

if (isset($_REQUEST['aop'])) {
    $rideboard->admin();
} else {
    $rideboard->user();
}

?>