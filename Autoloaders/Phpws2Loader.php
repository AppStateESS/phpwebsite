<?php

/**
 * Phpws2Loader
 * Responsible for loading legacy classes in the '\phpws2' namespace from the
 * /src-phpws2 directory.
 *
 * @author Jeremy Booker
 * @package Canopy
 */
function Phpws2Loader($class_name)
{
    // Class name must start with the 'phpws2\' namespace. If not, we pass and hope another autoloader can help
    if(substr($class_name, 0, strlen('phpws2\\')) !== 'phpws2\\'){
        return false;
    }

    $file_path = PHPWS_SOURCE_DIR . 'src-phpws2/src/' . str_replace('\\', '/', str_replace('phpws2\\', '', $class_name)) . '.php';

    if (is_readable($file_path)) {
        require_once $file_path;
        return true;
    } else {
        return false;
    }
}
