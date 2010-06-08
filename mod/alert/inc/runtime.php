<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (core\Core::atHome()) {
    \core\Core::initModClass('alert', 'Alert.php');
    $alert = new Alert;
    $alert->viewItems();
}

?>