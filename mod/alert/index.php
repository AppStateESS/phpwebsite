<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    header('location: ../../index.php');
    exit();
}

PHPWS_Core::initModClass('alert', 'Alert.php');
$alert = new Alert;

if (isset($_REQUEST['aop'])) {
    $alert->admin();
} else {
    $alert->user();
}

?>