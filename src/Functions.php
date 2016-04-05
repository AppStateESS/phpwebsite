<?php

/**
 * Procedural functions used throughout Beanie that run in the global namespace.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Returns true is value parameter is an associative array.
 * Copied from the php.net website.
 *
 * Note: this function is flawed. If an array is numerically keyed from zero to
 * count($array) - 1 this function will return false. It doesn't matter if the
 * numbers are cast as strings as PHP changes them to integers. If there is a chance
 * that array(0 => 'foo', 1 => 'bar') will EVER be passed to this function,
 * do not rely on it.
 *
 * @deprecated
 * @param array $value
 * @return boolean
 * @author Anonymous
 */
function is_assoc($value)
{
    return (is_array($value) && (0 !== count(array_diff_key($value,
                            array_keys(array_keys($value)))) || count($value) == 0));
}

/**
 * @param string $file Full path to file
 * @return string Returns the octal notation of the file
 */
function get_file_permission($file)
{
    $stat = stat($file);
    return sprintf("%o", ($stat['mode'] & 000777));
}

/**
 * Receives a printf formatted string and substitutes the values in the
 * $arr array.
 *
 * Example:
 * $arr[] = array('dogs', 'cats', 'mice');
 * $arr[] = array('cars', 'trucks', 'motorcycles');
 * $str = 'I like %s, %s, and %s.<br />';
 * echo vsprintf_array($str, $arr);
 *
 * Prints:
 * I like dogs, cats, and mice.
 * I like cars, trucks, and motorcycles.
 *
 * @param type $string
 * @param array $arr
 * @param string $join
 * @return string
 */
function vsprintf_array($string, array $arr, $join = null)
{
    if (!$join || !is_string($join)) {
        $join = "\n";
    }
    if (!is_string($string)) {
        throw Exception(t('First parameter is not a string'));
    }
    foreach ($arr as $values) {
        $rows[] = vsprintf($string, $values);
    }
    return implode($join, $rows);
}

/**
 * Same functionality as vsprintf_array, but echoes the result instead of
 * returning it. Not sure if replicating that function using printf instead
 * would be faster.
 *
 * @param type $string
 * @param array $arr
 * @param type $join
 */
function vprintf_array($string, array $arr, $join = null)
{
    echo vsprintf_array($string, $arr, $join);
}

/**
 * Based on code copied from php.net.
 * I pulled out Craig's switch for echoing elsewhere.
 * Substitute function until PHP releases their version.
 *
 * @author craig at craigfrancis dot co dot uk
 */
if (!function_exists('http_response_code')) {

    function http_response_code($code = NULL)
    {
        if ($code !== NULL) {
            $text = get_status_text($code);
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }
        return $code;
    }

}

/**
 * Returns the status code associated with an http code.
 * Copied from php.net
 * @author craig at craigfrancis dot co dot uk
 * @param integer $code
 */
function get_status_text($code)
{
    switch ($code) {
        case 100: $text = 'Continue';
            break;
        case 101: $text = 'Switching Protocols';
            break;
        case 200: $text = 'OK';
            break;
        case 201: $text = 'Created';
            break;
        case 202: $text = 'Accepted';
            break;
        case 203: $text = 'Non-Authoritative Information';
            break;
        case 204: $text = 'No Content';
            break;
        case 205: $text = 'Reset Content';
            break;
        case 206: $text = 'Partial Content';
            break;
        case 300: $text = 'Multiple Choices';
            break;
        case 301: $text = 'Moved Permanently';
            break;
        case 302: $text = 'Moved Temporarily';
            break;
        case 303: $text = 'See Other';
            break;
        case 304: $text = 'Not Modified';
            break;
        case 305: $text = 'Use Proxy';
            break;
        case 400: $text = 'Bad Request';
            break;
        case 401: $text = 'Unauthorized';
            break;
        case 402: $text = 'Payment Required';
            break;
        case 403: $text = 'Forbidden';
            break;
        case 404: $text = 'Not Found';
            break;
        case 405: $text = 'Method Not Allowed';
            break;
        case 406: $text = 'Not Acceptable';
            break;
        case 407: $text = 'Proxy Authentication Required';
            break;
        case 408: $text = 'Request Time-out';
            break;
        case 409: $text = 'Conflict';
            break;
        case 410: $text = 'Gone';
            break;
        case 411: $text = 'Length Required';
            break;
        case 412: $text = 'Precondition Failed';
            break;
        case 413: $text = 'Request Entity Too Large';
            break;
        case 414: $text = 'Request-URI Too Large';
            break;
        case 415: $text = 'Unsupported Media Type';
            break;
        case 500: $text = 'Internal Server Error';
            break;
        case 501: $text = 'Not Implemented';
            break;
        case 502: $text = 'Bad Gateway';
            break;
        case 503: $text = 'Service Unavailable';
            break;
        case 504: $text = 'Gateway Time-out';
            break;
        case 505: $text = 'HTTP Version not supported';
            break;
        default:
            $text = 'Unknown http status code "' . htmlentities($code) . '"';
            break;
    }
    return $text;
}

/**
 * Returns true if variable is a string, numeric OR an object with a toString method
 * @param mixed $variable
 * @return boolean
 */
function is_string_like($variable)
{
    return (is_string($variable) || is_numeric($variable) || (is_object($variable) && method_exists($variable,
                    '__toString')));
}

/**
 * Returns a string composed of characters. For passwords consider confusables
 * set to FALSE.
 *
 * @param integer $characters Number of characters in string
 * @param boolean $confusables If true, use letters O and L and numbers 0 and 1
 * @param boolean $uppercase If true, include uppercase letters
 * @return string
 */
function randomString($characters = 8, $confusables = false, $uppercase = false)
{
    $characters = (int) $characters;
    $alpha = '0123456789abcdefghijklmnopqrstuvwxyz';

    if ($uppercase) {
        $alpha .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    if (!$confusables) {
        $alpha = preg_replace('/[1l0oO]/', '', $alpha);
    }

    srand((double) microtime() * 1000000);

    $char_count = strlen($alpha);

    for ($i = 0; $i < $characters; $i++) {
        $char = rand() % $char_count;
        $str[] = substr($alpha, $char, 1);
    }
    return implode('', $str);
}


/**
 * Returns a string describing a current regular expression error.
 *
 * @param integer $code
 * @return string
 */
function preg_error_msg($code)
{
    switch ($code) {
        case PREG_NO_ERROR:
            return t('no error');

        case PREG_INTERNAL_ERROR:
            return t('internal PCRE error');

        case PREG_BACKTRACK_LIMIT_ERROR:
            return t('backtrack limit error');

        case PREG_RECURSION_LIMIT_ERROR:
            return t('recursion limit error');

        case PREG_BAD_UTF8_ERROR:
            return t('bad UTF-8 error');

        case PREG_BAD_UTF8_OFFSET_ERROR:
            return t('bad UTF-8 offset error');

        default:
            return t('unknown regular expression error');
    }
}

/**
 * Requires a file without echoing the content
 * @param string $file
 * @return string If the file is not php, returns the result.
 */
function safeRequire($file, $once = true)
{
    return safeFile($file, $once ? 'require_once' : 'require');
}

/**
 * Requires a file without echoing the content
 * @param string $file
 * @return string If the file is not php, returns the result.
 */
function safeInclude($file, $once = true)
{
    return safeFile($file, $once ? 'include_once' : 'include');
}

/**
 * Includes or requires a file. Returns an array of variables defined in the file
 * and any content echoed within.
 * @param string $file Path to file
 * @param string $type Type of require or include to use
 * @return array
 */
function safeFile($file, $type = 'require')
{
    ob_start();
    switch ($type) {
        case 'include':
            include $file;
            break;
        case 'include_once':
            include $file;
            break;
        case 'require':
            require $file;
            break;
        case 'require_once':
            require_once $file;
            break;
    }
    unset($file);
    unset($type);
    $arr['variables'] = get_defined_vars();
    $arr['string'] = ob_get_contents();
    ob_end_clean();
    return $arr;
}
