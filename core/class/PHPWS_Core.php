<?php

/**
 * Controls module manipulation
 *
 * Loads modules and their respective files.
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

if (!defined('FORCE_MOD_CONFIG')) {
    define('FORCE_MOD_CONFIG', true);
}

if (!defined('ALLOW_SCRIPT_TAGS')) {
    define('ALLOW_SCRIPT_TAGS', false);
}

if (!defined('LOG_DIRECTORY')) {
    define('LOG_DIRECTORY', PHPWS_SOURCE_DIR . 'logs/');
}

require_once PHPWS_SOURCE_DIR . 'core/inc/errorDefines.php';
PHPWS_Core::initCoreClass('PHPWS_Error.php');
class PHPWS_Core {

    /**
     * Loads each module's /inc/init.php file
     */
    public static function initializeModules()
    {
        if (!$moduleList = PHPWS_Core::getModules()) {
            throw new \Exception(t('No active active modules installed'));
        }
        if (PHPWS_Error::isError($moduleList)) {
            throw new \Exception($moduleList->getMessage());
        }

        foreach ($moduleList as $mod) {
            PHPWS_Core::setCurrentModule($mod['title']);
            /* Using include instead of require to prevent broken mods from hosing the site */
            $includeFile = PHPWS_SOURCE_DIR . 'mod/' . $mod['title'] . '/inc/init.php';

            if (is_file($includeFile)) {
                include($includeFile);
            }

            $GLOBALS['Modules'][$mod['title']] = $mod;
        }
    }

    /**
     * Loads each module's inc/close.php file
     */
    public static function closeModules()
    {
        if (!isset($GLOBALS['Modules'])) {
            PHPWS_Error::log(PHPWS_NO_MODULES, 'core', 'runtimeModules');
            PHPWS_Core::errorPage();
        }

        foreach ($GLOBALS['Modules'] as $mod){
            $includeFile = PHPWS_SOURCE_DIR . 'mod/' . $mod['title'] . '/inc/close.php';
            if (is_file($includeFile)) {
                include($includeFile);
            }
        }
    }

    /**
     * Gets all the modules from the module table
     */
    public static function getModules($active=true, $just_title=false)
    {
        $DB = new PHPWS_DB('modules');
        if ($active == true) {
            $DB->addWhere('active', 1);
        }
        $DB->addOrder('priority asc');

        if ($just_title==true) {
            $DB->addColumn('title');
            return $DB->select('col');
        } else {
            return $DB->select();
        }
    }

    /**
     * Returns an associative array of all the modules in the
     * module table
     * Array is indexed with the module title. The value of each
     * row is the module's proper name
     */
    public static function getModuleNames()
    {
        if (isset($GLOBALS['Core_Module_Names'])) {
            return $GLOBALS['Core_Module_Names'];
        }

        $db = new PHPWS_DB('modules');
        $db->setIndexBy('title');
        $db->addOrder('proper_name');
        $db->addColumn('proper_name');
        $db->addColumn('title');
        $result = $db->select('col');
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        $GLOBALS['Core_Module_Names'] = $result;
        return $GLOBALS['Core_Module_Names'];
    }

    /**
     * Returns a module object based on core
     */
    public static function loadAsMod($use_file=true)
    {
        PHPWS_Core::initCoreClass('Module.php');
        $core_mod = new PHPWS_Module('core', $use_file);
        return $core_mod;
    }


    /**
     * Loads each module's inc/runtime.php file
     */
    public static function runtimeModules()
    {
        if (!isset($GLOBALS['Modules'])) {
            PHPWS_Error::log(PHPWS_NO_MODULES, 'core', 'runtimeModules');
            PHPWS_Core::errorPage();
        }

        foreach ($GLOBALS['Modules'] as $title=>$mod) {
            PHPWS_Core::setCurrentModule($title);
            $runtimeFile = PHPWS_SOURCE_DIR . 'mod/' . $mod['title'] . '/inc/runtime.php';
            is_file($runtimeFile) ? include_once $runtimeFile : NULL;
        }
    }

    /**
     * Loads the index.php file of the currently selected module
     */
    public static function runCurrentModule()
    {
        if (isset($_REQUEST['module'])) {
            $mods = PHPWS_Core::getModules(true, true);
            if (!in_array($_REQUEST['module'], $mods)) {
                PHPWS_Core::home();
            }
            PHPWS_Core::setCurrentModule($_REQUEST['module']);
            $modFile = PHPWS_SOURCE_DIR . 'mod/' . $_REQUEST['module'] . '/index.php';
            if (is_file($modFile)) {
                include $modFile;
            } else {
                PHPWS_Core::errorPage('404');
            }
        }
    }

    /**
     * Requires a module's class file once
     * Returns true is successful, false otherwise
     */
    public static function initModClass($module, $file)
    {
        $classFile = PHPWS_SOURCE_DIR . 'mod/' . $module . '/class/' . $file;

        // If the requested file doesn't exist, throw an exception
        if (!is_file($classFile)) {
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', __CLASS__ . '::' .__FUNCTION__, "File: $classFile");
            throw new Exception(dgettext('core', 'Could not initialize module class ' . $classFile));
        }

        // Require the file, and catch any exceptions that might cause
        try{
            require_once $classFile;
        }catch(Exception $e){
            // Log the exception
            PHPWS_Error::log($e->getMessage());
            // Re-throw the exception
            throw $e;
        }

        return true;
    }


    /**
     * Requires a core class file once
     * Returns true is successful, false otherwise
     */
    public static function initCoreClass($file)
    {
        $classFile = PHPWS_SOURCE_DIR . 'core/class/' . $file;

        // If the requested file doesn't exist, throw an exception
        if (!is_file($classFile)) {
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', 'initCoreClass', "File: $classFile");
            require_once 'PEAR/Exception.php';
            throw new PEAR_Exception(dgettext('core', 'Could not initialize core class ' . $classFile));
        }

        // Require the file, and catch any exceptions that might cause
        try{
            require_once $classFile;
        }catch (Exception $e){
            // Log the exception
            PHPWS_Error::log($e->getMessage());
            // Re-throw the exception
            throw $e;
        }

        return true;
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

    /**
     * Makes a post key to track past posts
     * Works with setLastPost and isPosted
     */
    public static function _getPostKey()
    {
        $key = serialize($_POST);

        if (isset($_FILES)) {
            foreach ($_FILES as $file){
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
    public static function isPosted($return_count=false)
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

    public static function atHome()
    {
        if (isset($_REQUEST['module']) || isset($_POST['module']) || isset($_GET['module'])) {
            return false;
        } else {
            return true;
        }
    }

    public static function bookmark($allow_authkey=true)
    {
        $url = PHPWS_Core::getCurrentUrl();

        if(!$allow_authkey && preg_match('/authkey=/', $url)) {
            $url = null;
        }

        $_SESSION['PHPWS_Bookmark'] = $url;

        PHPWS_Core::pushUrlHistory();
    }

    public static function returnToBookmark($clear_bm=true)
    {
        if (isset($_SESSION['PHPWS_Bookmark'])) {
            $bm = $_SESSION['PHPWS_Bookmark'];
            if ($clear_bm) {
                $_SESSION['PHPWS_Bookmark'] = null;
                unset($_SESSION['PHPWS_Bookmark']);
            }
            PHPWS_Core::reroute($bm);
        } else {
            PHPWS_Core::goBack();
        }
    }

    public static function pushUrlHistory()
    {
        if(!isset($_SESSION['PHPWS_UrlHistory'])) {
            $_SESSION['PHPWS_UrlHistory'] = array();
        }

        array_push($_SESSION['PHPWS_UrlHistory'], PHPWS_Core::getCurrentUrl());
    }

    public static function popUrlHistory()
    {
        if(!isset($_SESSION['PHPWS_UrlHistory']) || count($_SESSION['PHPWS_UrlHistory']) == 0) {
            PHPWS_Core::home();
        }

        PHPWS_Core::reroute(array_pop($_SESSION['PHPWS_UrlHistory']));
    }

    /**
     * Returns the user browser to the referer (last web page)
     */
    public static function goBack()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            // prevent an endless loop
            $current_url = PHPWS_Core::getCurrentUrl(false);
            if (strtolower($current_url) == strtolower($_SERVER['HTTP_REFERER'])) {
                PHPWS_Core::home();
            } else {
                PHPWS_Core::reroute($_SERVER['HTTP_REFERER']);
            }
        } else {
            PHPWS_Core::home();
        }
    }

    /**
     * Sends the user to the home page (index.php)
     */
    public static function home()
    {
        PHPWS_Core::reroute();
    }

    /**
     * Returns a url prefix dependent on the security
     */
    public static function getHttp()
    {
        if ( isset($_SERVER['HTTPS']) &&
        strtolower($_SERVER['HTTPS']) == 'on' ) {
            return 'https://';
        } else {
            return 'http://';
        }
    }

    /**
     * Sends a location header based on the relative link passed
     * to the function.
     */
    public static function reroute($address=NULL)
    {
        $current_url = PHPWS_Core::getCurrentUrl();

        if ($current_url == $address) {
            return;
        }

        // Set last post since we will be skipping it
        PHPWS_Core::setLastPost();

        if (!preg_match('/^http/', $address)) {
            $address = preg_replace('@^/@', '', $address);
            $http = PHPWS_Core::getHttp();

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
    }

    /**
     * Kills a current page session
     */
    public static function killSession($sess_name)
    {
        $_SESSION[$sess_name] = NULL;
        unset($_SESSION[$sess_name]);
    }

    /**
     * Kills all sessions currently loaded
     */
    public static function killAllSessions()
    {
        $_SESSION = array();
        unset($_SESSION);
        session_destroy();
    }// END FUNC killAllSessions()

    /**
     * Returns true is a module is installed, false otherwise
     */
    public static function moduleExists($module)
    {
        return isset($GLOBALS['Modules'][$module]);
    }

    /**
     * Returns the currently active module
     */
    public static function getCurrentModule()
    {
        return $GLOBALS['PHPWS_Current_Mod'];
    }

    /**
     * Sets the currently active module
     */
    public static function setCurrentModule($module)
    {
        $GLOBALS['PHPWS_Current_Mod'] = $module;
    }

    /**
     * Retrieves a module's config file path. If the file
     * does not exist, throws an exception otherwise.
     */
    public static function getConfigFile($module, $file=NULL)
    {
        if (empty($file)) {
            $file = 'config.php';
        }

        $file = preg_replace('/[^\-\w\.\\\\\/]/', '', $file);
        $module = preg_replace('/[^\w\.]/', '', $module);

        if ($module == 'core') {
            $file = PHPWS_SOURCE_DIR . 'core/conf/' . $file;
        } else {
            $file = PHPWS_SOURCE_DIR . "mod/$module/conf/$file";
        }

        if (!is_file($file)) {
            return false;
        }

        return $file;
    }


    /**
     * Pseudoname of configRequireOnce
     */
    public static function requireConfig($module, $file=NULL, $exitOnError=true)
    {
        return PHPWS_Core::configRequireOnce($module, $file, $exitOnError);
    }


    /**
     * Like requireConfig but for files in the inc directory
     */
    public static function requireInc($module, $file, $exitOnError=true)
    {
        if ($module == 'core') {
            $inc_file = sprintf('%score/inc/%s', PHPWS_SOURCE_DIR, $file);
        } else {
            $inc_file = sprintf('%smod/%s/inc/%s', PHPWS_SOURCE_DIR, $module, $file);
        }

        if (!is_file($inc_file)) {
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', 'requireInc', $inc_file);
            if ($exitOnError) {
                PHPWS_Core::errorPage();
            }
            else {
                return false;
            }
        } else {
            require_once $inc_file;
        }

        return true;
    }


    /**
     * Loads a config file via a require. If missing, shows error page.
     * If file is NULL, function assumes 'config.php'
     */
    public static function configRequireOnce($module, $file=NULL, $exitOnError=true)
    {
        if (empty($file)) {
            $file = 'config.php';
        }
        $config_file = PHPWS_Core::getConfigFile($module, $file);

        if (empty($config_file) || !$config_file) {
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', 'configRequireOnce', $file);
            if ($exitOnError) {
                PHPWS_Core::errorPage();
            }
            else {
                return $config_file;
            }
        } else {
            require_once $config_file;
        }

        return true;
    }

    /**
     * Uses the Pear log class to write a log file to the logs directory
     */
    public static function log($message, $filename, $type=NULL)
    {

        if (!is_writable(LOG_DIRECTORY)) {
            exit(_('Unable to write to log directory.'));
        }

        if (is_file(LOG_DIRECTORY . $filename) && !is_writable(LOG_DIRECTORY . $filename)) {
            exit(sprintf(_('Unable to write %s file.'), $filename));
        }

        $conf = array('mode' => LOG_PERMISSION, 'timeFormat' => LOG_TIME_FORMAT);

        if (PHPWS_Core::isBranch()) {
            $branch_name = Branch::getCurrentBranchName();
            $message = '{' . $branch_name . '} ' . $message;
        } else {
            $message = '{HUB} ' . $message;
        }
        logMessage($message, $filename);
    }

    /**
     * Routes the user to a HTML file. File depends on code passed to it.
     */
    public static function errorPage($code=NULL)
    {
        switch ($code) {
            case '400':
                header('HTTP/1.0 400 Bad Request');
                include PHPWS_SOURCE_DIR . 'core/conf/400.html';
                break;

            case '403':
                header('HTTP/1.0 403 Forbidden');
                include PHPWS_SOURCE_DIR . 'core/conf/403.html';
                break;

            case '404':
                header('HTTP/1.0 404 Not Found');
                include PHPWS_SOURCE_DIR . 'core/conf/404.html';
                break;

            case 'overpost':
                include PHPWS_SOURCE_DIR . 'core/conf/overpost.html';
                break;

            default:
                header('HTTP/1.1 503 Service Unavailable');
                include PHPWS_SOURCE_DIR . 'core/conf/error_page.html';
                break;
        }
        exit();
    }

    /**
     * Returns true if server OS is Windows
     */
    public static function isWindows()
    {
        if (isset($_SERVER['WINDIR']) ||
        preg_match('/(microsoft|win32)/i', $_SERVER['SERVER_SOFTWARE'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * If a file is posted beyond php's posting limits, it will drop the
     * POST without an error message. checkOverPost sends the user to an
     * overpost error page.
     */
    public static function checkOverPost()
    {
        if (!isset($_GET['check_overpost'])) {
            return true;
        } elseif (empty($_POST) && isset($_SERVER['CONTENT_LENGTH'])) {
            Security::log(_('User tried to post a file beyond server limits.'));
            PHPWS_Core::errorPage('overpost');
        }

        return true;
    }

    /**
     * Returns an array of the core modules. Set from the core_modules.php file.
     */
    public static function coreModList()
    {
        static $core_modules = NULL;

        if (is_array($core_modules)) {
            return $core_modules;
        }

        $file = PHPWS_Core::getConfigFile('core', 'core_modules.php');
        if (PHPWS_Error::isError($file)) {
            return $file;
        }

        include $file;
        return $core_modules;
    }

    /**
     * Returns an array of all installed modules
     */
    public static function installModList($active_only=false)
    {
        $db = new PHPWS_DB('modules');
        if ($active_only) {
            $db->addWhere('active', 1);
        }
        $db->addColumn('title');
        return $db->select('col');
    }

    /**
     * Returns an array with containing all the values of
     * the passed object.
     */
    public static function stripObjValues($object, $strip_null=true)
    {
        $className = get_class($object);
        $classVars = get_class_vars($className);
        $var_array = NULL;

        if(!is_array($classVars)) {
            return PHPWS_Error::get(PHPWS_CLASS_VARS, 'core',
                                    'PHPWS_Core::stripObjValues', $className);
        }

        foreach ($classVars as $key => $value) {
            if ($strip_null && !isset($object->$key)) {
                continue;
            }
            $var_array[$key] = $object->$key;
        }

        return $var_array;
    }

    /**
     * Plugs an array of $variables into the $object. The associative array
     * keys must be identical to the object's variable names.
     *
     * 5/17/06 Removed the code that prevent private variables from loading.
     * Added 10/15/2008:
     * If arguments are sent in the third parameter, plugObject will call
     * the object's postPlug function and send those arguments to it.
     */
    public static function plugObject($object, $variables, $args=null)
    {
        $post_plug = isset($args) && method_exists($object, 'postPlug');

        $className = get_class($object);
        $classVars = get_class_vars($className);

        if(!is_array($classVars) || empty($classVars)) {
            return PHPWS_Error::get(PHPWS_CLASS_VARS, 'core', 'PHPWS_Core::plugObject', $className);
        }

        if (isset($variables) && !is_array($variables)) {
            return PHPWS_Error::get(PHPWS_WRONG_TYPE, 'core', __CLASS__ . '::' . __FUNCTION__, gettype($variables));
        }

        foreach($classVars as $key => $value) {
            if(isset($variables[$key])) {
                if (preg_match('/^[aO]:\d+:/', $variables[$key])) {
                    $object->$key = unserialize($variables[$key]);
                } else {
                    $object->$key = $variables[$key];
                }
            }
        }

        if ($post_plug) {
            $object->postPlug($args);
        }
        return true;
    }

    /**
     * Returns the installation's home directory
     */
    public static function getHomeDir()
    {
        $address[] = $_SERVER['DOCUMENT_ROOT'];
        $address[] = dirname($_SERVER['PHP_SELF']);
        return implode('', $address) . '/';
    }

    /**
     * Returns the installations url address
     */
    public static function getHomeHttp($with_http=true, $with_directory=true, $with_slash=true)
    {
        if ($with_http && $with_directory && $with_slash && defined('PHPWS_HOME_HTTP')) {
            return PHPWS_HOME_HTTP;
        }

        if ($with_http) {
            $address[] = PHPWS_Core::getHttp();
        }
        $address[] = $_SERVER['HTTP_HOST'];

        if ($with_directory) {
            $address[] = dirname($_SERVER['PHP_SELF']);
        }

        $url = implode('', $address);
        $url = preg_replace('@\\\@', '/', $url);

        if ($with_slash && !preg_match('/\/$/', $url)) {
            $url .= '/';
        }
        return $url;
    }

    /**
     * I am tired of writing this over and over with the php
     * version differences.
     *
     * Returns true if the object is of the entered class.
     * The class name must be lower case. If it isn't well you should
     * have known PHP 5 was going to change the rules, on get_class
     * shouldn't have ya? In other words, My_Class and my_class are
     * the same as far as this function is concerned.
     * Mix up your class names.
     */
    public static function isClass(&$object, $class_name)
    {
        if (!is_object($object)) {
            return false;
        }

        if (strtolower(get_class($object)) == strtolower($class_name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns the full phpwebsite release version
     */
    public static function releaseVersion()
    {
        include PHPWS_SOURCE_DIR . 'core/conf/version.php';
        return $version;
    }

    /**
     * Returns the core version.
     *
     * @param boolean get_file  If true, uses the boost.php file, if false
     *                          uses the database version.
     */
    public static function getVersionInfo($get_file=true)
    {

        $file = PHPWS_SOURCE_DIR . 'core/boost/boost.php';
        include $file;

        if (!$get_file) {
            if (!PHPWS_DB::isTable('core_version')) {
                $version = '1.0.0';
            } else {
                $db = new PHPWS_DB('core_version');
                $db->addColumn('version');
                $version = $db->select('one');
            }
        }

        return array('proper_name'  => $proper_name,
                     'version'      => $version,
                     'version_http' => $version_http);
    }

    public static function isRewritten()
    {
        return strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']) === FALSE;
    }


    /**
     * Returns the url of the current page
     * If redirect is true and a redirect occurs at the root level,
     * index.php is returned.
     */
    public static function getCurrentUrl($relative=true, $use_redirect=true)
    {
        if (!$relative) {
            $address[] = PHPWS_Core::getHomeHttp();
        }

        $self = & $_SERVER['PHP_SELF'];

        if ($use_redirect && self::isRewritten()) {
            // some users reported problems using redirect_url so parsing uri instead
            if ($_SERVER['REQUEST_URI'] != '/') {
                $root_url = substr($self, 0, strrpos($self, '/'));
                $address[] = preg_replace("@^$root_url/@", '', $_SERVER['REQUEST_URI']);
            } else {
                $address[] = 'index.php';
            }
            return implode('', $address);
        }

        $stack = explode('/', $self);
        if ($url = array_pop($stack)) {
            $address[] = $url;
        }

        if (!empty($_SERVER['QUERY_STRING'])) {
            $address[] = '?';
            $address[] = $_SERVER['QUERY_STRING'];
        }
        if (!empty($address)) {
            $address = implode('', $address);
            return preg_replace('@^/?@', '', $address);
        }
    }

    /**
     * Returns true if the site is a hub or if the site is
     * an allowed branch. If false is returned, the index file
     * drops the user to an error page. Also sets the Is_Branch GLOBAL
     */
    public static function checkBranch()
    {
        if (substr(php_sapi_name(),0,3) == 'cgi' && PHPWS_SOURCE_DIR == getcwd() . '/') {
            $GLOBALS['Is_Branch'] = false;
            return true;
        } elseif(str_ireplace('index.php', '', $_SERVER['SCRIPT_FILENAME']) == PHPWS_SOURCE_DIR) {
            $GLOBALS['Is_Branch'] = false;
            return true;
        } else {
            if (!PHPWS_Core::initModClass('branch', 'Branch.php')) {
                PHPWS_Error::log(PHPWS_HUB_IDENTITY, 'core', 'Cannot load Branch class');
                return false;
            }
            if (Branch::checkCurrentBranch()) {
                $GLOBALS['Is_Branch'] = true;
                return true;
            } else {
                PHPWS_Error::log(PHPWS_HUB_IDENTITY, 'core', 'Hash not found: ' . SITE_HASH . ' from ' . getcwd());
                return false;
            }
        }
    }

    /**
     * Will return true if the current process is a branch accessing the
     * hub files.
     * If a module needs to check if it is in the hub working on a branch, PHPWS_Boost::inBranch
     * should be used instead.
     */
    public static function isBranch()
    {
        if (!isset($GLOBALS['Is_Branch'])) {
            return false;
        }
        return $GLOBALS['Is_Branch'];
    }

    public static function allowScriptTags()
    {
        if (ALLOW_SCRIPT_TAGS && class_exists('Current_User') &&
        Current_User::allow('users', 'scripting')) {
            return true;
        } else {
            return false;
        }
    }


    public static function securePopup()
    {
        if (!class_exists('Layout')) {
            exit(_('Unable to display contents.'));
        }

        // no session has been created, return false
        if (!isset($_SESSION['secure_open_window']) ||
        !isset($_GET['owpop']) ||
        !in_array($_GET['owpop'], $_SESSION['secure_open_window'])) {

            javascript('close_refresh');
            Layout::metaRoute('index.php');
            Layout::nakedDisplay(_('Unable to display contents.'));
            return false;
        }

        javascript('secure_pop', array('id'=>$_GET['owpop']));
        return true;
    }

    public static function getBaseURL()
    {
        return PHPWS_Core::getHttp()
        . $_SERVER['HTTP_HOST']
        . preg_replace('/index.*/', '', $_SERVER['PHP_SELF']);
    }
}// End of core class

?>
