<?php

PHPWS_Settings::load('profiler');

if (!isset($_REQUEST['module'])) {
    PHPWS_Core::initModClass('profiler', 'Profiler.php');
    Profiler::view();
}


?>