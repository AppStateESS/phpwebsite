<?php

function Phpws2Loader($class_name)
{
    // Class name must start with the 'phpws\' namespace. If not, we pass and hope another autoloader can help
    // This is faster than searching the n-element $not_found array, so we'll fail faster by checking this before searching the array
    if(substr($class_name, 0, strlen('phpws2\\')) !== 'phpws2\\'){
        return false;
    }

    // Keep a static list of classes that we know this autoloader can't resolve
    static $not_found = array();
    if (in_array($class_name, $not_found)) {
        return;
    }

    $file_path = PHPWS_SOURCE_DIR . 'src-phpws2/src/' . str_replace('\\', '/', str_replace('phpws2\\', '', $class_name)) . '.php';

    if (is_readable($file_path)) {
        require_once $file_path;
        return true;
    } else {
        return false;
        $not_found[] = $class_name;
    }
}
