<?php

if (isset($_SESSION['Access_Shortcut_Enabled'])) {
    $key = Key::getCurrent();
    if (!empty($key) && !$key->isDummy()) {
        PHPWS_Core::initModClass('access', 'Access.php');
        Access::shortcut();
    }
 }


?>