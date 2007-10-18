<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (PHPWS_Core::atHome()) {
    PHPWS_Core::initModClass('alert', 'Alert.php');
    $alert = new Alert;
    $alert->viewItems();
}

?>