<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Previously an extension of the Exception class, Error was changed to a
 * class of static methods. The addition of PDO meant there would have been
 * three exceptions types (Error, PDOException, and the base Exception) which
 * would be a pain to pick in a catch. Much easier to just catch(Exception $e)
 * which will work with PDO.
 */
class Error {

    /**
     * A sipmle exception handler for catching exceptions that are thrown outside
     * the main execution try/catch block (e.g. when autoloading). This function
     * is registered with PHP using the set_exception_handler() function at the
     * start of index.php.
     *
     * @param Exception $e
     */
    public static function exceptionHandler(\Exception $error)
    {
        self::log($error);
        if (DISPLAY_ERRORS) {
            echo '<h1>Unhandled exception:</h1><pre>', self::getErrorInfo($error,
                    true, true), '</pre>';
        } else {
            self::errorPage();
        }
        die();
    }

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
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (SHOW_ALL_ERRORS || ($errno & (E_ERROR | E_PARSE | E_USER_ERROR))) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }

    /**
     *
     * @param Exception $error
     * @param integer $code
     */
    public static function errorPage($code = 500)
    {
        http_response_code($code);
        $error_text = get_status_text($code);
        $default = PHPWS_SOURCE_DIR . 'Global/Error/Pages/' . ERROR_PAGE_TEMPLATE;
        $error_template = PHPWS_SOURCE_DIR . "Global/Error/Pages/$code.html";
        $source_http = PHPWS_SOURCE_HTTP;
        if (is_file($error_template)) {
            include $error_template;
        } else {
            include $default;
        }
        exit();
    }

    /**
     * Writes the error message to a log file.
     */
    public static function log(Exception $error)
    {
        try {
            self::logError(self::getErrorInfo($error, LOG_ERROR_STACK));
        } catch (\Exception $e) {
            // very bad error, could not even log it
            trigger_error($e->getMessage(), E_USER_ERROR);
            exit();
        }
    }

    public static function logError($message)
    {
        if (defined('PHPWS_DSN')) {
            $message = str_replace(PHPWS_DSN, '-- DSN removed --', $message);
        }
        logMessage($message, 'error.log');
    }

    /**
     * Returns a line describing the error and where it occurred.
     * @return string
     */
    private static function getErrorInfo(Exception $error, $error_stack = true, $xdebug = false)
    {
        // Windows doesn't do %T
        $time = strftime('%Y%m%d-%H:%M:%S');
        $file = $error->getFile();
        $line = $error->getLine();
        if ($xdebug) {
            $file_info = self::xdebugLink($file, $line);
            $trace = self::xdebugTraceString($error);
        } else {
            $file_info = & $file;
            $trace = $error->getTraceAsString();
        }

        if ($error_stack) {
            return sprintf("[%s] %s in %s on line %s\n%s\n\n", $time,
                    $error->getMessage(), $file_info, $line, $trace);
        } else {
            return sprintf("[%s] %s in %s on line %s\n\n", $time,
                    $error->getMessage(), $file_info, $line);
        }
    }

    private static function xdebugTraceString(\Exception $error)
    {
        $class = $type = NULL;
        $trace = $error->getTrace();

        foreach ($trace as $key => $value) {
            extract($value);
            $row[] = "#$key " . self::xdebugLink($file, $line) . "($line): $class$type$function";
        }
        return implode("<br>", $row);
    }

    private static function xdebugLink($file, $line)
    {
        return '<a href="xdebug://' . $file . '@' . $line . '">' . $file . '</a>';
    }

    /**
     * Displays the toString of the exception object with pre tags.
     * Copy of Data::debug()
     * @param Exception $exception
     * @param boolean $terminate If true, die after echo
     */
    public static function debug($exception, $terminate = false)
    {
        $content = '<pre>' . $exception->__toString() . '</pre>';
        if ($terminate) {
            echo $content;
            exit();
        } else {
            echo $content;
        }
    }

}

?>
