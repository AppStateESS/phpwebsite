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
     * @todo finish error information
     * @param \Error $error
     */
    public static function exceptionHandler(\Exception $error)
    {
        self::log($error);
        if (DISPLAY_ERRORS) {
            echo '<h1>DISPLAY_ERRORS is set to TRUE</h1><pre>', self::getErrorInfo($error, true, true), '</pre>';
        } else {
            self::errorPage();
        }
        die();
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
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
        $default = 'Global/Error/Pages/' . ERROR_PAGE_TEMPLATE;
        $error_template = "Global/Error/Pages/$code.html";
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
            echo $e->getMessage();
            exit();
        }
    }

    public static function logError($message)
    {
        $log_path = ERROR_LOG_DIRECTORY . 'error.log';
        if (!@error_log($message, 3, $log_path)) {
            throw new \Exception('Could not write error.log file. Check error directory setting and file permissions.');
        } else {
            chmod($log_path, LOG_FILE_PERMISSION);
            return true;
        }
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
            return sprintf("[%s] %s in %s on line %s\n%s\n\n", $time, $error->getMessage(), $file_info, $line, $trace);
        } else {
            return sprintf("[%s] %s in %s on line %s\n\n", $time, $error->getMessage(), $file_info, $line);
        }
    }

    private static function xdebugTraceString(\Exception $error)
    {
        $class = $type = NULL;
        $trace = $error->getTrace();

        foreach ($trace as $key=>$value) {
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