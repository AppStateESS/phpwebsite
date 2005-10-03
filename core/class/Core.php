<?php

/**
 * Controls module manipulation
 *
 * Loads modules and their respective files.
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */


class PHPWS_Core {

    function initializeModules(){
        if (!$moduleList = PHPWS_Core::getModules()) {
            PHPWS_Error::log(PHPWS_NO_MODULES, 'core', 'initializeModules');
            PHPWS_Core::errorPage();
        }

        if (PEAR::isError($moduleList)) {
            PHPWS_Error::log($moduleList);
            PHPWS_Core::errorPage();
        }
    
        foreach ($moduleList as $mod){
            PHPWS_Core::setCurrentModule($mod['title']);

            /* Check to see if this is an older module */
            if ($mod['pre94'] == 1) {
                PHPWS_Crutch::initializeModule($mod['title']);
                $GLOBALS['pre094_modules'][] = $mod['title'];
                $GLOBALS['Modules'][$mod['title']] = $mod;
            }

            /* Using include instead of require to prevent broken mods from hosing the site */
            $includeFile = PHPWS_SOURCE_DIR . 'mod/' . $mod['title'] . '/inc/init.php';

            if (is_file($includeFile)) {
                include($includeFile);
            }

            $GLOBALS['Modules'][$mod['title']] = $mod;
        }
    }

    function closeModules(){
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

        if (isset($GLOBALS['pre094_modules'])) {
            PHPWS_Crutch::closeSessions();
        }
    }


    function getModules($active=TRUE, $just_title=FALSE){
        $DB = new PHPWS_DB('modules');
        if ($active == TRUE) {
            $DB->addWhere('active', 1);
        }
        $DB->addOrder('priority asc');

        if ($just_title==TRUE) {
            $DB->addColumn('title');
            return $DB->select('col');
        } else {
            return $DB->select();
        }
    }

    function runtimeModules(){
        if (!isset($GLOBALS['Modules'])) {
            PHPWS_Error::log(PHPWS_NO_MODULES, 'core', 'runtimeModules');
            PHPWS_Core::errorPage();
        }

        foreach ($GLOBALS['Modules'] as $title=>$mod) {
            if (isset($GLOBALS['pre094_modules'])) {
                if (!isset($GLOBALS['Crutch_Session_Started']) && 
                    in_array($title, $GLOBALS['pre094_modules'])) {
                    PHPWS_Crutch::startSessions();
                }
        
                if (isset($GLOBALS['Crutch_Sessions'][$title])) {
                    foreach ($GLOBALS['Crutch_Sessions'][$title] as $session_name => $class_name) {
                        if (!isset($_SESSION[$session_name])) {
                            $_SESSION[$session_name] = & new $class_name;
                        }
                    }
                }

            }
            PHPWS_Core::setCurrentModule($title);
            $runtimeFile = PHPWS_SOURCE_DIR . 'mod/' . $mod['title'] . '/inc/runtime.php';
            is_file($runtimeFile) ? include_once $runtimeFile : NULL;
        }
    }


    function runCurrentModule(){
        if (isset($_REQUEST['module'])) {
            PHPWS_Core::setCurrentModule($_REQUEST['module']);
            $modFile = PHPWS_SOURCE_DIR . 'mod/' . $_REQUEST['module'] . '/index.php';
            if (is_file($modFile)) {
                include $modFile;
            }
        }
    }


    function initModClass($module, $file){
        $classFile = PHPWS_SOURCE_DIR . 'mod/' . $module . '/class/' . $file;
        if (is_file($classFile)) {
            require_once $classFile;
            return TRUE;
        }
        else {
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', __CLASS__ . '::' .__FUNCTION__, "File: $classFile");
            return FALSE;
        }
    }

    function initCoreClass($file){
        $classFile = PHPWS_SOURCE_DIR . 'core/class/' . $file;
        if (is_file($classFile)) {
            require_once $classFile;
            return TRUE;
        }
        else {
            PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, 'core', 'initCoreClass', "File: $classFile");
            return FALSE;
        }
    }

    function setLastPost(){
        if (!PHPWS_Core::isPosted()) {
            $key = PHPWS_Core::_getPostKey();
            $_SESSION['PHPWS_LastPost'][] = $key;
            if (count($_SESSION['PHPWS_LastPost']) > MAX_POST_TRACK) {
                array_shift($_SESSION['PHPWS_LastPost']);
            }
        }
    }

    function _getPostKey(){
        $key = serialize($_POST);

        if (isset($_FILES)) {
            foreach ($_FILES as $file){
                extract($file);
                $key .= $name . $type . $size;
            }
        }

        return md5($key);
    }

    function isPosted(){
        if (!isset($_SESSION['PHPWS_LastPost']) || !isset($_POST)) {
            return FALSE;
        }
        $key = PHPWS_Core::_getPostKey();
        return in_array($key, $_SESSION['PHPWS_LastPost']);
    }
 
    function goBack()
    {
        PHPWS_Core::reroute($_SERVER['HTTP_REFERER']);
    }

    function home(){
        PHPWS_Core::reroute();
    }

    function getHttp(){
        if ( isset($_SERVER['HTTPS']) &&
             strtolower($_SERVER['HTTPS']) == 'on' ) {
            return 'https://';
        } else {
            return 'http://';
        }
    }

    function reroute($address=NULL){
        if (!preg_match('/^http/', $address)) {
            $http = PHPWS_Core::getHttp();

            $dirArray = explode('/', $_SERVER['PHP_SELF']);
            array_pop($dirArray);
            $dirArray[] = '';
      
            $directory = implode('/', $dirArray);
      
            $location = $http . $_SERVER['HTTP_HOST'] . $directory . $address;
        } else
            $location = &$address;

        $location = preg_replace('/&amp;/', '&', $location);
        header('Location: ' . $location);
        exit();
    }

    function killSession($sess_name){
        $_SESSION[$sess_name] = NULL;
        unset($_SESSION[$sess_name]);
    }

    function killAllSessions(){
        $_SESSION = array();
        unset($_SESSION);
        session_destroy();
    }// END FUNC killAllSessions()

    function moduleExists($module){
        return isset($GLOBALS['Modules'][$module]);
    }

    function getCurrentModule(){
        return $GLOBALS['PHPWS_Current_Mod'];
    }

    function setCurrentModule($module){
        $GLOBALS['PHPWS_Current_Mod'] = $module;
    }

    function getConfigFile($module, $file){
        $file = preg_replace('/[^\-\w\.\\\\\/]/', '', $file);
        $module = preg_replace('/[^\w\.]/', '', $module);

        if ($module == 'core') {
            $altfile = PHPWS_SOURCE_DIR . 'config/core/' . $file;
            $file = './config/core/' . $file;
        }
        else {
            $altfile = PHPWS_SOURCE_DIR . 'mod/' . $module . '/conf/' . $file;
            $file = './config/' . $module . '/' . $file;
        }

        if (!is_file($file) || FORCE_MOD_CONFIG) {
            if (!is_file($altfile)) {
                return FALSE;
            }
            else
                $file = $altfile;
        }

        return $file;
    }

    function requireConfig($module, $file=NULL, $exitOnError=TRUE)
    {
        return PHPWS_Core::configRequireOnce($module, $file, $exitOnError);
    }

    /**
     * Loads a config file. If missing, shows error page
     */
    function configRequireOnce($module, $file=NULL, $exitOnError=TRUE)
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

        return TRUE;
    }

    function &loadAsMod(){
        PHPWS_Core::initCoreClass('Module.php');
    
        $core = & new PHPWS_Module;
        $core->setTitle('core');
        $core->setDirectory(PHPWS_SOURCE_DIR . 'core/');
        $file = PHPWS_Core::getConfigFile('core', 'version.php');
        if (PEAR::isError($file)) {
            return $file;
        } else {
            include $file;
        }

        $core->setVersion($version);
        $core->setRegister(FALSE);
        $core->setImportSQL(TRUE);
        $core->setProperName('Core');

        return $core;
    }

    function log($message, $filename, $type=NULL){
        require_once 'Log.php';

        if (!is_writable(PHPWS_LOG_DIRECTORY)) {
            exit('Unable to write to log directory ' . PHPWS_LOG_DIRECTORY);
        }

        $conf = array('mode' => LOG_PERMISSION, 'timeFormat' => LOG_TIME_FORMAT);
        $log  = &Log::singleton('file', PHPWS_LOG_DIRECTORY . $filename, $type, $conf, PEAR_LOG_NOTICE);

        if (PEAR::isError($log)) {
            return;
        }


        $log->log($message, PEAR_LOG_NOTICE);
        $log->close();
    }

    function errorPage($code=NULL) {
        switch ($code) {
        case '400':
            include 'config/core/400.html';
            break;

        case '403':
            include 'config/core/403.html';
            break;

        case '404':
            include 'config/core/404.html';
            break;

        default:
            include 'config/core/error_page.html';
            break;
        }
        exit();
    }

    function isWindows(){
        if (isset($_SERVER['WINDIR']) ||
            preg_match('/(microsoft|win32)/i', $_SERVER['SERVER_SOFTWARE'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function checkSecurity(){
        if (CHECK_DIRECTORY_PERMISSIONS == TRUE &&
            !isset($_SESSION['SECURE'])) {
            if (is_writable('./config/') || is_writable('./templates/')) {
                PHPWS_Error::log(PHPWS_DIR_NOT_SECURE, 'core');
                PHPWS_Core::errorPage();
            }
        }
    }

    function coreModList(){
        $file = PHPWS_Core::getConfigFile('core', 'core_modules.php');
        if (PEAR::isError($file)) {
            return $file;
        }

        include $file;
        return $core_modules;
    }

    function installModList(){
        $db = & new PHPWS_DB('modules');
        $db->addColumn('title');
        return $db->select('col');
    }

    function stripObjValues($object){
        $className = get_class($object);
        $classVars = get_class_vars($className);
        $var_array = NULL;

        if(!is_array($classVars)) {
            return PHPWS_Error::get(PHPWS_CLASS_VARS, 'core',
                                    'PHPWS_Core::stripObjValues', $className);
        }

        foreach ($classVars as $key => $value) {
            if (isset($object->$key)) {
                $var_array[$key] = $object->$key;
            }
        }

        return $var_array;
    }
 

    function plugObject(&$object, $variables){
        $className = get_class($object);
        $classVars = get_class_vars($className);

        if(!is_array($classVars) || empty($classVars)) {
            return PHPWS_Error::get(PHPWS_CLASS_VARS, 'core', 'PHPWS_Core::plugObject', $className);
        }

        if (isset($variables) && !is_array($variables)) {
            return PHPWS_Error::get(PHPWS_WRONG_TYPE, 'core', __CLASS__ . '::' . __FUNCTION__, gettype($variables));
        }


        foreach($classVars as $key => $value) {
            $column = $key;
            if($column[0] == '_') {
                $column = substr($column, 1, strlen($column));
            }
      
            if(isset($variables[$column])) {
                if (preg_match('/^[aO]:\d+:/', $variables[$column])) {
                    $object->$key = unserialize($variables[$column]);
                } else {
                    $object->$key = $variables[$column];
                }
            }
        }
        return TRUE;
    }

    function getHomeDir()
    {
        $address[] = $_SERVER['DOCUMENT_ROOT'];
        $address[] = dirname($_SERVER['PHP_SELF']);
        return implode('', $address) . '/';
    }

    function getHomeHttp()
    {
        $address[] = PHPWS_Core::getHttp();
        $address[] = $_SERVER['HTTP_HOST'];
        $address[] = dirname($_SERVER['PHP_SELF']);
        return implode('', $address) . '/';
    }

    function getCurrentUrl($relative=TRUE, $use_redirect=TRUE)
    {
        if (!$relative) {
            $address[] = PHPWS_Core::getHomeHttp();
        } 

        if (isset($_SERVER['REDIRECT_URL']) && $use_redirect) {
            $address[] = str_ireplace(dirname($_SERVER['PHP_SELF']) . '/', '', $_SERVER['REDIRECT_URL']);
            return implode('', $address);
        } else {
            $url = $_SERVER['PHP_SELF'];
        }

        $address[] = str_ireplace(dirname($_SERVER['PHP_SELF']) . '/', '', $_SERVER['PHP_SELF']);

        if (!empty($_SERVER['QUERY_STRING'])) {
            $address[] = '?';
            $address[] = $_SERVER['QUERY_STRING'];
        }

        return implode('', $address);
    }

}// End of core class

?>