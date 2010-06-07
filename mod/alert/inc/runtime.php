<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (Core\Core::atHome()) {
    Core\Core::initModClass('alert', 'Alert.php');
    $alert = new Alert;
    $alert->viewItems();
}

?>