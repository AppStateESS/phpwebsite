<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (ini_get('session.auto_start')) {
    echo 'session.auto_start must be set to 0 for phpWebSite to work. Please review your php.ini file.';
    exit();
 }

chdir('../');
if (isWindows()) {
    ini_set('include_path', '.;.\\lib\\pear\\');
 } else {
    ini_set('include_path', '.:./lib/pear/');
 }

if (!is_file('lib/pear/DB.php')) {
    echo 'Unable to locate your pear library files.';
    echo '<br />';
    echo 'Untar pear.tgz in your phpwebsite installation directory.';
    echo '<br />';
    echo '<pre>tar zxf pear.tgz</pre>';
    exit();
}

if (!extension_loaded('gd')) {
    echo 'You need to compile the <a href="http://www.libgd.org/Main_Page">GD image library</a> into PHP.';
    echo '<br />File Cabinet will not function properly with out it.';
    exit();
}


if (!is_dir('config/core/') || !is_file('config/core/language.php')) {
    require 'core/class/File.php';
    if (!is_writable('config/')) {
        echo 'Please make your config/ directory writable to continue.<br />';
        exit();
    } else {
        // in case they run php 4
        require_once 'lib/pear/Compat/Function/scandir.php';
        PHPWS_File::copy_directory('core/conf/', 'config/core/');
        if (!is_dir('config/core/')) {
            echo 'Unable to copy core/conf/ directory to config/core/. Please do so manually.';
            exit();
        }
    }
}

if (isset($_REQUEST['step']) && $_REQUEST['step'] > 1) {
    if (!is_file('./config/core/config.php')) {
        header('location: index.php');
        exit();
    } else {
        require_once './config/core/config.php';
    }
 }
 else {
     require_once './setup/preconfig.php';
 }

require_once './inc/Functions.php';
require_once './core/class/Init.php';
include_once './setup/config.php';
require_once './setup/class/Setup.php';

PHPWS_Core::initCoreClass('Form.php');
PHPWS_Core::initCoreClass('Text.php');
PHPWS_Core::initCoreClass('Template.php');
PHPWS_Core::initModClass('boost', 'Boost.php');
PHPWS_Core::initModClass('users', 'Current_User.php');

session_start();
$forward = false;
$content = array();
$setup = & new Setup;

include 'core/conf/version.php';
$title = "phpWebSite $version - ";

if (!$setup->checkSession($content) || !isset($_REQUEST['step'])) {
    $step = 0;
 } else {
    $step = $_REQUEST['step'];
 }

if (!$setup->checkDirectories($content)){
    $title .= _('Directory Permissions');
    exit(Setup::show($content, $title));
 }

switch ($step){
 case '0':
     $title .=  _('Site Setup');
     $setup->welcome($content);
     break;

 case '1':
     $title .= _('Create Config File');
     $setup->createConfig($content);
     break;

 case '1a':
     $title .= _('Create Database');
     $setup->createDatabase($content);
     break;

 case '1b':
     $_SESSION['configSettings']['database'] = TRUE;
     $dsn = Setup::getDSN(2);
     Setup::setConfigSet('dsn', $dsn);
     $title .= _('Create Config File');
     $setup->createConfig($content);
     break;

 case '2':
     $title .= _('Create Core');
     $result = $setup->createCore($content);
     break;

 case '3':
     $title .= _('Install Modules');
     $result = $setup->installModules($content);
     if ($result) {
         $setup->finish($content);
     } elseif ($_SESSION['Boost']->currentDone()) {
         $forward = true;
     }


     break;
 }

echo Setup::show($content, $title, $forward);

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

?>