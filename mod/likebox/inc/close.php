<?php

/**
 * Close.php - Handles including the HTML for the buttons after other modules are finished
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

\phpws\PHPWS_Core::initModClass('likebox', 'LikeboxView.php');
\phpws\PHPWS_Core::initModClass('likebox', 'LikeboxSettings.php');

$settings = LikeboxSettings::getInstance();


if($settings->get('enabled') == 1){
    $view = new LikeboxView($settings);
    $view->view();
}

