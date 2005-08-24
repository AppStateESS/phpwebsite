<?php

if (isset($_REQUEST['command'])) {
    PHPWS_Core::initModClass('profiler', 'Profiler.php');
    Profiler::user();
} else {

    if (!Current_User::authorized('profiler')) {
        Current_User::disallow();
    }
    
    PHPWS_Core::initModClass('profiler', 'Profiler.php');
    
    Profiler::admin();
}

?>