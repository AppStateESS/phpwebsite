<?php

/**
 * Canopy Autoloader
 * Looks for core Canopy classes from the \Canopy... namespace and tries to load
 * them out of the /src... directory.
 *
 * This is the autoloader for all new Canopy code moving forward.
 * @author Jeremy Booker
 * @package Canopy
 */
function CanopyLoader($class_name)
{
    // Class name must start with the 'Canop\' namespace. If not, we pass and hope another autoloader can help
    if(substr($class_name, 0, strlen('Canopy\\')) !== 'Canopy\\'){
        return false;
    }

    $file_path = PHPWS_SOURCE_DIR . 'src/' . str_replace('\\', '/', str_replace('Canopy\\', '', $class_name)) . '.php';

    if (is_readable($file_path)) {
        require_once $file_path;
        return true;
    } else {
        return false;
    }
}
