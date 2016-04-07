<?php

if(\phpws\PHPWS_Core::moduleExists('analytics')) {
    \phpws\PHPWS_Core::initModClass('analytics', 'Analytics.php');
    Analytics::injectTrackers();
}

