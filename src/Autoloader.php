<?php

if (!defined('STRICT_AUTOLOADER')) {
    define('STRICT_AUTOLOADER', false);
}

spl_autoload_register('phpwsOldLoad');
if (!STRICT_AUTOLOADER) {
    spl_autoload_register('phpwsNewLoad');
}

function phpwsNewLoad($class_name)
{
    static $files_found = array();

    if (isset($files_found[$class_name])) {
        return;
    }
    
    $class_array = explode('\\', $class_name);
    $class_dir = array_shift($class_array);
    
    $base_dir = PHPWS_SOURCE_DIR . "src/$class_dir/autoload.php";

    if (is_file($base_dir)) {
        require_once $base_dir;
    }
}

function phpwsOldload($class_name)
{
    // stores previously found requires
    static $files_found = array();

    if (isset($files_found[$class_name])) {
        // If class was found, we require and move on
        require_once $files_found[$class_name];
        return;
    }
    $class_name = preg_replace('@^/|/$@', '', str_replace('\\', '/', $class_name));
    $new_mod_file = PHPWS_SOURCE_DIR . preg_replace('|^([^/]+)/([\w/]+)|', 'mod/\\1/class/\\2.php', $class_name);
    $global_file = PHPWS_SOURCE_DIR . 'Global/' . $class_name . '.php';
    $class_file = PHPWS_SOURCE_DIR . 'core/class/' . $class_name . '.php';
    if (is_file($new_mod_file)) {
        $files_found[$class_name] = $new_mod_file;
        require_once $new_mod_file;
    } elseif (is_file($global_file)) {
        $files_found[$class_name] = $global_file;
        require_once $global_file;
    } elseif (is_file($class_file)) {
        $files_found[$class_name] = $class_file;
        require_once $class_file;
    } elseif (isset($_REQUEST['module'])) {
        $module = preg_replace('/\W/', '', $_REQUEST['module']);

        if (preg_match("/^$module\//i", $class_name)) {
            $class_name = preg_replace("/^$module\//i", '', $class_name);
        }

        $class_file = PHPWS_SOURCE_DIR . "mod/$module/class/$class_name.php";

        if (is_file($class_file)) {
            $files_found[$class_name] = $class_file;
            require_once $class_file;
        }
    }
}
