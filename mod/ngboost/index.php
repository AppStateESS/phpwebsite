<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @author Hilmar Runge <ngwebsite.net>
*/

	$prod=false;
	$prod?error_reporting(0):error_reporting(-1);

    if (!defined('PHPWS_SOURCE_DIR')) {
        include '../../core/conf/404.html';
        exit();
    }
	
    if (!isset($_REQUEST['action'])) {
        PHPWS_Core::errorPage(404);
    }

    PHPWS_Core::requireConfig('ngboost');
	
	// && also take classical boost conditions
	PHPWS_Core::requireConfig('boost');
    if (DEITY_ACCESS_ONLY && !Current_User::isDeity()) {
        Current_User::disallow();
    }
    if (!Current_User::authorized('ngboost')) {
        Current_User::disallow();
    }

	PHPWS_Core::initModClass('ngboost', 'ngAction.php');
	$ngboost = new ngBoost_Action;
	$ngboost->index();

?>