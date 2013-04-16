<?php

/**
 * Class to assist running old version of phpwebsite modules.
 * @author
 */
class Backward {

    public static function load()
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }
        $loaded = true;

        self::requireBackwardClasses();
        self::defineBackwardVariables();
    }

    /**
     * Defines the four basic phpwebsite directory and http variables based on
     * values in beanie.
     */
    public static function defineBackwardVariables()
    {
        if (!defined('PHPWS_SOURCE_HTTP')) {
            define('PHPWS_SOURCE_HTTP', SHARED_ASSETS);
        }
        if (!defined('PHPWS_SOURCE_DIR')) {
            define('PHPWS_SOURCE_DIR', ROOT_DIRECTORY);
        }
        if (!defined('PHPWS_HOME_HTTP')) {
            /**
             *  If running Backward from the command line, this portion would
             *  fail
             */
            if (isset($_SERVER['HTTP_HOST'])) {
                define('PHPWS_HOME_HTTP', Server::getSiteUrl());
            } else {
                define('PHPWS_HOME_HTTP', './');
            }
        }
        if (!defined('PHPWS_HOME_DIR')) {
            define('PHPWS_HOME_DIR', SITE_DIRECTORY);
        }
    }

    /**
     * Requires the most commonly used Backward classes.
     */
    public static function requireBackwardClasses()
    {
        require_once 'Global/Backward/Class/Functions.php';
        require_once 'Global/Backward/Class/PHPWS_Error.php';
        require_once 'Global/Backward/Class/PHPWS_Core.php';
        require_once 'Global/Backward/Class/PHPWS_DB.php';
        require_once 'Global/Backward/Class/PHPWS_Template.php';
        require_once 'Global/Backward/Class/PHPWS_Link.php';
        require_once 'Global/Backward/Class/File.php';
        require_once 'Global/Backward/Class/PHPWS_Text.php';
        require_once 'Global/Backward/Class/Key.php';
        require_once 'Global/Backward/Class/Form.php';
        require_once 'Global/Backward/Class/PHPWS_Settings.php';
        require_once 'Global/Backward/Class/Current_User.php';
        require_once 'Global/Backward/Class/Layout.php';
        require_once 'mod/controlpanel/class/ControlPanel.php';
    }

}

?>
