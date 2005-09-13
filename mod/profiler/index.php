<?php
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if (isset($_REQUEST['user_cmd'])) {
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