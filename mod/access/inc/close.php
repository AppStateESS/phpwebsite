<?php

if (isset($_SESSION['Access_Shortcut_Enabled'])) {
    $key = Key::getCurrent();
    if (!empty($key) && !$key->isHomeKey()) {
        PHPWS_Core::initModClass('access', 'Access.php');
        Access::shortcut();
    }
 }


?>