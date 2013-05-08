<?php

/*
 * Main controller class for the project
 *
 * @author Jeremy Booker
 * @package
 */

class ModuleController {

    public function __construct()
    {

    }

    public function execute()
    {
        ob_start();
        require_once PHPWS_SOURCE_DIR . 'config/core/source.php';
        require_once PHPWS_SOURCE_DIR . 'core/class/Init.php';
        require_once PHPWS_SOURCE_DIR . 'inc/Forward.php';

        PHPWS_Core::requireConfig('core', 'file_types.php');
        PHPWS_Core::initializeModules();

        define('SESSION_NAME', md5(SITE_HASH . $_SERVER['REMOTE_ADDR']));
        session_name(SESSION_NAME);
        session_start();

        require_once PHPWS_SOURCE_DIR . 'inc/Security.php';

        if (!PHPWS_Core::checkBranch()) {
            PHPWS_Core::errorPage();
        }

        PHPWS_Core::runtimeModules();
        PHPWS_Core::checkOverpost();
        PHPWS_Core::runCurrentModule();
        PHPWS_Core::closeModules();

        PHPWS_Core::pushUrlHistory();

        PHPWS_DB::disconnect();

        PHPWS_Core::setLastPost();

        if (isset($_REQUEST['reset'])) {
            PHPWS_Core::killAllSessions();
        }
    }
}
