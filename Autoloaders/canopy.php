<?php

function CanopyLoader($class_name)
{
    if(substr($class_name, 0, strlen('Canopy\\')) !== 'Canopy\\'){
        return false;
    }

    static $not_found = array();
    if (in_array($class_name, $not_found)) {
        return;
    }

    $file_path = PHPWS_SOURCE_DIR . 'src/' . str_replace('\\', '/', str_replace('Canopy\\', '', $class_name)) . '.php';

    if (is_readable($file_path)) {
        require_once $file_path;
        return true;
    } else {
        return false;
        $not_found[] = $class_name;
    }
}
