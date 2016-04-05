<?php

/*
spl_autoload_register(function($class){
    $directory = PHPWS_SOURCE_DIR . 'src/';
    $class_name = str_replace('\\', '/', $class);
    $core_class_file = $directory . 'phpws/' . $class_name . '.php';
    $global_class_file = $directory . 'phpws2/' . $class_name . '.php';
    echo $core_class_file;
    echo '<br>';
    echo $global_class_file;
    if (is_file($core_class_file)) {
        require_once $core_class_file;
    } else if (is_file($global_class_file)) {
        require_once $global_class_file;
    }
});
*/

spl_autoload_register('phpwsAutoload');

function phpwsAutoload($class_name)
{
    // stores previously found requires
    static $files_found = array();

    if (isset($files_found[$class_name])) {
        // If class was found, we require and move on
        require_once $files_found[$class_name];
        return;
    }
    $class_name = preg_replace('@^/|/$@', '',
            str_replace('\\', '/', $class_name));
    $new_mod_file = PHPWS_SOURCE_DIR . preg_replace('|^([^/]+)/([\w/]+)|',
                    'mod/\\1/class/\\2.php', $class_name);
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

        /**
         * If the module name is the first token in the class name,
         * then remove it. Example:
         * Class name: 'Intern/Command/ShowAddInternship'
         * Should map to file: 'phpwebsite/mod/intern/class/Command/ShowAddInternship.php'
         *
         * In this example, we need to remove 'Intern/' from the class name in order to generate
         * the correct file name.
         */
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