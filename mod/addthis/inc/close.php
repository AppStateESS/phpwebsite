<?php

/**
 * Close.php - Handles including the HTML for the buttons after other modules are finished
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('addthis', 'AddThisView.php');
PHPWS_Core::initModClass('addthis', 'Settings.php');

$settings = Settings::getInstance();

if($settings->get('enabled') == 1){
    $view = new AddThisView($settings);
    $view->view();
}

?>
