<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
//ini_set('display_errors', 'On');
//error_reporting (-1);

chdir('../');
set_include_path('lib/pear/');
require_once 'Global/Functions.php';

define('CONFIG_CREATED', is_file('config/core/config.php'));
if (CONFIG_CREATED) {
	require_once './config/core/config.php';
} else {
	define('SITE_HASH', md5(rand()));
}
require_once './setup/config.php';
require_once './core/class/Init.php';
require_once './core/class/Template.php';
require_once './setup/class/Setup.php';

// Core is loaded in Init
PHPWS_Core::initCoreClass('Form.php');
PHPWS_Core::initModClass('boost', 'Boost.php');
PHPWS_Core::initModClass('users', 'Current_User.php');

$setup = new Setup;

/**
 * Starts session, checks if supported on client and server
 * Program exits here if fails.
 */
$setup->checkSession();

/**
 * Check the server for certain settings before getting into the meat
 * of the installation. Will return if successfully or previously passed.
 */
$setup->checkServerSettings();
$setup->goToStep();


exit('end of switch');
/**
 * Returns true if server OS is Windows
 */
function isWindows()
{
	if (isset($_SERVER['WINDIR']) ||
	preg_match('/(microsoft|win32)/i', $_SERVER['SERVER_SOFTWARE'])) {
		return TRUE;
	} else {
		return FALSE;
	}
}


function fakeCore()
{
	exit('fakecore');
	if (!function_exists('setLanguage')) {
		function setLanguage(){}
	}

	set_include_path('./lib/pear/');
	define('PHPWS_LOG_DIRECTORY', './logs/');
	define('DEFAULT_LANGUAGE', 'en_US');
	define('CURRENT_LANGUAGE', 'en_US');
	define('PHPWS_SOURCE_DIR', getcwd() . '/');
	define('LOG_PERMISSION', 0600);
	define('PHPWS_LOG_ERRORS', true);
	define('LOG_TIME_FORMAT', '%X %x');
	require_once './core/class/Core.php';
	require_once './core/class/Error.php';
	require_once './core/class/File.php';
}

/**
 * Checks to see if the server check was passed
 * @return unknown_type
 */
function serverPass($content)
{
	exit('serverpass');
	if (!isset($_COOKIE['check_server']) || !$_COOKIE['check_server']) {
		if (checkServer($content)) {
			$content[] = dgettext('core','Server passed enough tests to allow installation.');
			$content[] = sprintf('<p><a href="index.php">%s</a></p>', dgettext('core','Continue...'));
			setcookie('check_server', 1, 0);
		} else {
			$content[] = dgettext('core','Server failed crucial tests. You may not install phpWebSite.');
			setcookie('check_server', 0, 0);
		}

		display(dgettext('core', 'Checking server'), implode('<br />', $content));
	}
}




?>