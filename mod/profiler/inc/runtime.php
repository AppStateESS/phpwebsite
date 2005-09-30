<?php

if (!isset($_REQUEST['module'])) {
    PHPWS_Core::initModClass('profiler', 'Profiler.php');
    Profiler::view();
}


?>