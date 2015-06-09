#!/usr/bin/php
<?php
/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
process($_SERVER['argv']);

function process($arguments)
{
    $hash = null;
    $name = null;
    $module = null;
    $file_directory = null;

    array_shift($arguments);
    if (!isset($arguments[0])) {
        $arguments[0] = '-h';
    }

    for ($i = 0; $i < count($arguments); $i++) {
        if ($arguments[$i] == '-h') {
            print_help();
            exit;
        } elseif ($arguments[$i] == '-f') {
            $i++;
            if (!isset($arguments[$i])) {
                exit("Configuration file not included.\n");
            }
            $file_directory = $arguments[$i];
        } elseif ($arguments[$i] == '-H') {
            $i++;
            if (!isset($arguments[$i])) {
                exit("Hash not included.\n");
            }
            $hash = $arguments[$i];
        } elseif ($arguments[$i] == '-n') {
            $i++;
            if (!isset($arguments[$i])) {
                exit("Name not included.\n");
            }
            $name = $arguments[$i];
        } elseif ($arguments[$i] == '-m') {
            $i++;
            if (!isset($arguments[$i])) {
                exit("Module not included.\n");
            }
            $module = $arguments[$i];
        }
    }

    if (!isset($file_directory)) {
        echo 'Error: You must include a file directory. See "pulse.php -h"';
    }
    include_config_file($file_directory);
    chdir(PHPWS_SOURCE_DIR);
    
    // Helps with Security include
    $_SERVER['REQUEST_URI'] = 'pulse.php';
    
    require_once PHPWS_SOURCE_DIR . 'inc/Bootstrap.php';
    require_once PHPWS_SOURCE_DIR . 'core/conf/defines.php';
    require_once PHPWS_SOURCE_DIR . 'mod/pulse/class/PulseController.php';
    try {
        $request = new \Request('index.php', Request::GET);
        if (!empty($hash)) {
            $request->setVar('hash', $hash);
        }
        if (!empty($name)) {
            $request->setVar('name', $name);
        }
        if (!empty($module)) {
            $request->setVar('schedule_module', $module);
        }
        \pulse\PulseController::runSchedules($request);
    } catch (\Exception $e) {
        echo "Error:\n";
        echo $e->getMessage();
        echo "\n\n";
    }
}

function print_help()
{
    echo <<<EOF

Runs schedules ready to be executed.
    
Usage: pulse.php -f directory/to/phpwebsite/config/file
    
Commands:
-f      Path to phpWebSite installation's database configuration file.
-H      Hash of schedule to run (if used, name and module are ignored)
-n      Name of schedule(s) to run
-m      Name of module to run. Only schedules of this module will be called.
    
If hash, name, and module are not set, all schedules will be run.
If not run as ROOT or APACHE, logs will not be written and you will get an error.
\n
EOF;
}

function include_config_file($file_directory)
{
    if (!is_file($file_directory)) {
        exit("Configuration file not found: $file_directory\n");
    }
    require_once $file_directory;
    if (!defined('PHPWS_DSN')) {
        exit("DSN not found\n");
    }
}
