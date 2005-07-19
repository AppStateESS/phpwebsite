<?php

if (!Current_User::authorized('profiler')) {
    Current_User::disallow();
}

PHPWS_Core::initModClass('profiler', 'Profiler.php');

Profiler::admin();

?>