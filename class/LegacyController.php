<?php
namespace phpws;

/*
 * LegacyController  - Main controller class for the project
 *
 * @author Jeremy Booker
 * @package TheThing
 */

use \PHPWS_Core;
use \PHPWS_DB;

class LegacyController {

    public function __construct()
    {

    }

    public function execute()
    {
        include PHPWS_SOURCE_DIR . 'phpws_stats.php';

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

        // BGmode
        // NEEDS MOAR COMMENTS
        if (isset($_SESSION['BG'])) {
            ob_end_clean();
            echo $_SESSION['BG'];
        } else {
            ob_end_flush();
        }

        PHPWS_DB::disconnect();

        PHPWS_Core::setLastPost();

        if (isset($_REQUEST['reset'])) {
            PHPWS_Core::killAllSessions();
        }

        // BGmode
        // NEEDS MOAR COMMENTS
        if (isset($_SESSION['BG'])) {
            unset($_SESSION['BG']);
        } else {
            show_stats();
        }
    }
}
