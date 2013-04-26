<?php

require_once 'Global/Backward/Inc/defines.php';

/**
 * Description of PHPWS_Core
 *
 * @author matt
 */
class PHPWS_Core {

    /**
     * Requires a configuration file based on module. exitOnError was removed
     * as we should always fail on an error
     * @param string $module
     * @param string $file
     */
    public static function configRequireOnce($module, $file = NULL)
    {
        $config_file = new \Variable\File(PHPWS_Core::getConfigFile($module, $file), 'config_file');
        $config_file->requireOnce();
        return true;
    }

    public static function getConfigFile($module, $file = NULL)
    {
        if (empty($file)) {
            $file = 'config.php';
        }

        $module = new \Variable\Attribute($module, 'module');

        if ($module->get() == 'core') {
            $file = 'Global/Backward/conf/' . $file;
        } else {
            $file = "mod/$module/conf/$file";
        }

        $config_file = new \Variable\File($file, 'config_file');
        if (!$config_file->exists()) {
            throw new \Exception(t('File not found: %s', $file));
        }

        return (string) $config_file;
    }

    /**
     * Requires a module's class file once
     * @param string $module
     * @param string $file
     * @return boolean
     * @throws \Exception
     */
    public static function initModClass($module, $file)
    {
        $class_file = new \Variable\File("mod/$module/class/$file", 'class_file');
        $class_file->requireOnce();
        return true;
    }

    public static function initCoreClass($file)
    {
        $class_file = new \Variable\File("Global/Backward/Class/$file", 'class_file');
        $class_file->requireOnce();
        return true;
    }

    /**
     *
     * @param string $module
     * @param string $file
     * @throws \Exception
     */
    public static function requireInc($module, $file)
    {
        //@todo may need to parse $module on certain calls

        if ($module == 'core') {
            $inc_file = 'Backward/Inc/' . $file;
        } else {
            $inc_file = 'mod/' . $module . '/inc/' . $file;
        }

        $inc_var = new \Variable\File($inc_file, 'requireInc');
        $inc_var->requireOnce();
        return true;
    }

    /**
     * Pseudoname of configRequireOnce
     */
    public static function requireConfig($module, $file = NULL)
    {
        return self::configRequireOnce($module, $file);
    }

    public static function getCurrentUrl($relative = true, $use_redirect = true)
    {
        Server::getCurrentUrl($relative, $use_redirect);
    }

    /**
     * Returns the installations url address
     * Ignoring with_slash as it will always be added
     */
    public static function getHomeHttp($with_http = true, $with_directory = true)
    {
        return Server::getSiteUrl($with_http, $with_directory);
    }

    public static function home()
    {
        PHPWS_Core::reroute();
    }

    public static function isRewritten()
    {
        return strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']) === FALSE;
    }

    /**
     * Sets the last form post made to the website.
     * Works with isPosted
     */
    public static function setLastPost()
    {
        $key = PHPWS_Core::_getPostKey();
        if (!PHPWS_Core::isPosted()) {
            $_SESSION['PHPWS_LastPost'][] = $key;
            if (count($_SESSION['PHPWS_LastPost']) > MAX_POST_TRACK) {
                array_shift($_SESSION['PHPWS_LastPost']);
            }
        } elseif (isset($_SESSION['PHPWS_Post_Count'][$key])) {
            if (isset($_SESSION['PHPWS_Post_Count'][$key])) {
                $_SESSION['PHPWS_Post_Count'][$key]++;
            } else {
                $_SESSION['PHPWS_Post_Count'][$key] = 1;
            }
        }
    }

    public static function reroute($address = null)
    {
        Server::forward($address);
        /*
        $current_url = Server::getCurrentUrl();

        if ($current_url == $address) {
            return;
        }

        // Set last post since we will be skipping it
        PHPWS_Core::setLastPost();

        if (!preg_match('/^http/', $address)) {
            $address = preg_replace('@^/@', '', $address);
            $http = Server::getHttp();

            $dirArray = explode('/', $_SERVER['PHP_SELF']);
            array_pop($dirArray);
            $dirArray[] = '';

            $directory = implode('/', $dirArray);
            $location = $http . $_SERVER['HTTP_HOST'] . $directory . $address;
        } else {
            $location = & $address;
        }

        $location = preg_replace('/&amp;/', '&', $location);
        header('Location: ' . $location);
        exit();
         *
         */
    }

    /**
     * Makes a post key to track past posts
     * Works with setLastPost and isPosted
     */
    public static function _getPostKey()
    {
        $key = serialize($_POST);
        $name = $type = $size = null;
        if (isset($_FILES)) {
            foreach ($_FILES as $file) {
                extract($file);
                $key .= $name . $type . $size;
            }
        }

        return md5($key);
    }

    /**
     * Checks to see if the currently post is in the LastPost
     * session. If so, it returns true. Function can be used to
     * prevent double posts.
     * If return_count is true, it returns the number of attempts
     * made with the same post.
     */
    public static function isPosted($return_count = false)
    {
        if (!isset($_SESSION['PHPWS_LastPost']) || !isset($_POST)) {
            return false;
        }

        $key = PHPWS_Core::_getPostKey();

        if (!isset($_SESSION['PHPWS_Post_Count'])) {
            $_SESSION['PHPWS_Post_Count'][$key] = 1;
        }

        $result = in_array($key, $_SESSION['PHPWS_LastPost']);

        if ($result && $return_count) {
            return $_SESSION['PHPWS_Post_Count'][$key];
        } else {
            return $result;
        }
    }

    /**
     * Returns a url prefix dependent on the security
     */
    public static function getHttp()
    {
        Server::getHttp();
    }

}

?>
