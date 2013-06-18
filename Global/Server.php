<?php

/**
 * Class to assist with _SERVER super globals.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Server {

    private static $REQUEST_SINGLETON;

    /**
     *
     * @return \Request
     */
    public static function getCurrentRequest()
    {
        if(is_null(self::$REQUEST_SINGLETON)) {
            $url    = self::getCurrentUrl();
            $method = $_SERVER['REQUEST_METHOD'];
            $vars   = $_REQUEST;
            $data   = file_get_contents('php://input');
            $accept = new Http\Accept($_SERVER['HTTP_ACCEPT']);

            self::$REQUEST_SINGLETON = new Request($url, $method, $vars, $data, $accept);
        }

        return self::$REQUEST_SINGLETON;
    }

    /**
     * Returns the beginning of a web address based on secure socket status.
     * @return string
     */
    public static function getHttp()
    {
        if (isset($_SERVER['HTTPS']) &&
                strtolower($_SERVER['HTTPS']) == 'on') {
            return 'https://';
        } else {
            return 'http://';
        }
    }

    /**
     * Returns the current user's ip address.
     * @return string
     */
    public static function getUserIp()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            throw new Exception(t('SERVER REMOTE ADDRESS not set'));
        }
    }

    /**
     * Returns the url of the current page
     * If redirect is true and a redirect occurs at the root level,
     * index.php is returned.
     * @param boolean $relative Returned site url does not contain web root.
     * @param boolean $use_redirect Returns the address as mod_rewrite format.
     * @return string
     */
    public static function getCurrentUrl($relative = true, $use_redirect = true)
    {
        if (!$relative) {
            $address[] = self::getSiteUrl();
        }

        $self = & $_SERVER['PHP_SELF'];

        if ($use_redirect && isset($_SERVER['REDIRECT_URL'])) {
            // some users reported problems using redirect_url so parsing uri instead
            if ($_SERVER['REQUEST_URI'] != '/') {
                $root_url = substr($self, 0, strrpos($self, '/'));
                $address[] = preg_replace("@^$root_url/@", '',
                        $_SERVER['REQUEST_URI']);
            } else {
                $address[] = 'index.php';
            }
            return implode('', $address);
        }

        $stack = explode('/', $self);
        $url = array_pop($stack);
        if (!empty($url)) {
            $address[] = $url;
        }

        if (!empty($_SERVER['QUERY_STRING'])) {
            $address[] = '?';
            $address[] = $_SERVER['QUERY_STRING'];
        }

        $address = implode('', $address);
        return preg_replace('@^/?@', '', $address);
    }

    /**
     *
     * @param boolean $with_http
     * @param boolean $with_directory
     * @return string
     */
    public static function getSiteUrl($with_http = true, $with_directory = true)
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            throw new Exception('$_SERVER[HTTP_HOST] superglobal does not exist');
        }
        if ($with_http) {
            $address[] = self::getHttp();
        }
        $address[] = $_SERVER['HTTP_HOST'];
        if ($with_directory) {
            $address[] = dirname($_SERVER['PHP_SELF']);
        }

        $url = preg_replace('@\\\@', '/', implode('', $address));
        $url .= '/';
        return $url;
    }

    /**
     * Sends the user to a new web page automatically based on the url.
     * @param string $url Address to forward to
     */
    public static function forward($url)
    {
        if (!preg_match('/^http(s)?:/i', $url)) {
            $url = self::getSiteUrl() . $url;
        }
        header('location: ' . $url);
        exit();
    }

    // @todo decide what to do for error pages
    public static function pageNotFound()
    {
        // @todo turn header back on
        header("HTTP/1.0 404 Not Found");
        echo '<html><head><title>404 - Page not found</title></head><body><h1>404 - Page not found</h1></body></html>';
    }

}

?>
