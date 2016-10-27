<?php

function CanopyLoader($class_name)
{
    static $not_found = array();
    if (in_array($class_name, $not_found)) {
        return;
    }

    $class_array = explode('\\', $class_name);
    $class_dir = array_shift($class_array);

    $base_dir = PHPWS_SOURCE_DIR . "src/$class_dir/autoload.php";

    if (is_file($base_dir)) {
        require_once $base_dir;
        return true;
    } else {
        $not_found[] = $class_name;
        return false;
    }
}
