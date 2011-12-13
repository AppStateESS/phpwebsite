<?php

if(PHPWS_Core::moduleExists('analytics')) {
    PHPWS_Core::initModClass('analytics', 'Analytics.php');
    Analytics::injectTrackers();
}

?>
