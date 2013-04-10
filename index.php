<?php

/**
 * Main file for loading phpwebsite. Loads configuration
 * and creates inital object to start execution.
 *
 * @link http://phpwebsite.appstate.edu/
 * @package phpws
 * @author Matthew McNaney <matt at tux dot appstate dot edu>,
 * @author Hilmar Runge <hi at dc4db dot net>
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU GPLv3
 * @copyright Copyright 2013, Appalachian State University & Contributors
 */

// Setup initial error handling so that errors at least
// end up in the web server's logs
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');

/* Debugging Flag
 * Setting this to true will cause exceptions to be echoed
 * to the browser, as welll as being logged. This is useful
 * for development environments, but should be set to false
 * for production work.
 */
define('DEBUG', true);

// If config file is present, then load it
// otherwise, go to setup.
if (is_file('config/core/config.php')) {
    require_once 'config/core/config.php';
} else {
    if (is_file('./setup/index.php')) {
        header('Location: ./setup/index.php');
        exit();
    } else {
        // Config file missing, Setup index.php missing, so stop here.
        exit('Fatal Error: Could not locate your configuration file and no setup index.php available.');
    }
}

// Now that we have file paths from configuration,
// setup autoloading and setup the namespace
spl_autoload_register('autoloadTheThing');

// Create a new Thing and run it for this request
use \phpws\LegacyController;
try{
    $controller = new LegacyController();
    $controller->execute();

}catch(Exception $e){ // Catch ALL the exceptions!
    exceptionHandler($e);
}

// Clean up after ourselves
spl_autoload_unregister('autoloadTheThing');
restore_exception_handler();
restore_error_handler();


/**
 * A simple error handler for catching major errors and turning them into exceptions
 * using PHP's built in ErrorException class.
 *
 * @param $errno Int - Error number
 * @param $errstr
 * @param $errfile
 * @param $errline
 * @param @errcontext
 */
function errorHandler($errno, $errstr, $errfile = null, $errline = null, $errcontext = null)
{
    // Ignore most types of errors, and only throw exceptions for the most critical.
    // NB: This ignores E_STRICT errors, because PEAR still has lots of those.
    if($errno & (E_ERROR | E_PARSE | E_USER_ERROR)){
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

/**
 * A sipmle exception handler for catching exceptions that are thrown outside
 * the main execution try/catch block (e.g. when autoloading). This function
 * is registered with PHP using the set_exception_handler() function at the
 * start of index.php.
 *
 * @param Exception $e
 */
function exceptionHandler(Exception $e)
{
    // Log the exception to the web server's log
    error_log("Exception: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");

    // If config had debug turned on, then echo the exception and exit
    if(DEBUG){
        echo $e;
        exit;
    }

    require_once('exception.html');
    exit();
}

/**
 * The central custom autoloader method for TheThing. Takes a class path
 * (namespace path + class name) and attempts to require_once() the proper
 * file containing that class.
 *
 * Currently, this will only load "core" classes from the /class directory.
 *
 * @param string $class Namespace path and class name
 */
function autoloadTheThing($class)
{
    $parts = explode('\\', $class);

    // Handle the new way of doing things
    if($parts[0] == 'phpws') {
        array_shift($parts); // Remove TheThing namespace, keep the rest of the path
        $path = PHPWS_SOURCE_DIR . '/class/' . implode('/', $parts) . '.php';
        // Can't use getcwd here because branches
        require_once $path;
        return;
    }

    // Handle the old way of doing things
    // TODO: deprecate
    if(substr($class, 0, 6) == 'PHPWS_') {
        $class_file = PHPWS_SOURCE_DIR . 'core/class/' . 
            preg_replace('/^PHPWS_/', '', $class) . '.php';
        if(!is_file($class_file)) {
            // TODO: Better Exceptions
            throw new Exception("Could not use the old autoloader to initialize class $class in file $class_file");
        }
        require_once $class_file;

        // Alert the developer that something old is happening
        $back = debug_backtrace();
        $file = $back[2]['file'];
        $line = $back[2]['line'];
        PHPWS_Error::log("Deprecation Notice: used old autoload to load $class in $file line $line");

        return;
    }
}
?>
