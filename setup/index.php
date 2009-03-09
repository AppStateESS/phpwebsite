<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

chdir('../');

define('SKIP_STEP_1', true);

$content = array();
if (!isset($_COOKIE['check_server']) || !$_COOKIE['check_server']) {
    if (checkServer($content)) {
        $content[] = _('Server passed enough tests to allow installation.');
        if (create_core_directories($content)) {
            $content[] = _('Core directories created.');
        } else {
            $content[] = _('Failed to create core directories. Please check your directory permissions in images, templates, and config.');
        }

        $content[] = sprintf('<p><a href="index.php">%s</a></p>', _('Continue...'));
        setcookie('check_server', 1, 0);
    } else {
        $content[] = _('Server failed crucial tests. You may not install phpWebSite.');
        setcookie('check_server', 0, 0);
    }
    echo implode('<br />', $content);
    exit();
}


if (isWindows()) {
    ini_set('include_path', '.;.\\lib\\pear\\');
 } else {
    ini_set('include_path', '.:./lib/pear/');
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
$setup = new Setup;

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

function checkServer(&$content)
{
    $allow_install = true;

    $test['session_auto_start']['pass'] = !(bool)ini_get('session.auto_start'); // need 0
    $test['session_auto_start']['fail'] = _('session.auto_start must be set to 0 for phpWebSite to work. Please review your php.ini file.');
    $test['session_auto_start']['name'] = _('Session auto start disabled');
    $test['session_auto_start']['crit'] = true;

    $test['pear_files']['pass'] = is_file('lib/pear/DB.php');
    $test['pear_files']['fail'] = _('Could not find Pear library files. You will need to download the pear package from our site at http://phpwebsite.appstate.edu/downloads/pear.zip and unzip it in your installation directory.');
    $test['pear_files']['name'] = _('Pear library installed');
    $test['pear_files']['crit'] = true;

    $test['gd']['pass'] = extension_loaded('gd');
    $test['gd']['fail'] = sprintf(_('You need to compile the %sGD image library%s into PHP.'), '<a href="http://www.libgd.org/Main_Page">', '</a>');
    $test['gd']['name'] = _('GD graphic libraries installed');
    $test['gd']['crit'] = true;

    $test['image_dir']['pass'] = is_dir('images/') && is_writable('images/');
    $test['image_dir']['fail'] = sprintf(_('%s directory does not exist or is not writable.'), 'SITENAME/images');
    $test['image_dir']['name'] = _('Image directory ready');
    $test['image_dir']['crit'] = true;

    $test['file_dir']['pass'] = is_dir('files/') && is_writable('files/');
    $test['file_dir']['fail'] = sprintf(_('%s directory does not exist or is not writable.'), 'SITENAME/files');
    $test['file_dir']['name'] = _('File directory ready');
    $test['file_dir']['crit'] = true;

    $test['template_dir']['pass'] = is_dir('templates/') && is_writable('templates/');
    $test['template_dir']['fail'] = sprintf(_('%s directory does not exist or is not writable.'), 'SITENAME/templates');
    $test['template_dir']['name'] = _('Template directory ready');
    $test['template_dir']['crit'] = true;

    $test['javascript_dir']['pass'] = is_dir('javascript/') && is_writable('javascript/');
    $test['javascript_dir']['fail'] = sprintf(_('%s directory does not exist or is not writable.'), 'SITENAME/javascript');
    $test['javascript_dir']['name'] = _('Javascript directory ready');
    $test['javascript_dir']['crit'] = true;

    $test['config_dir']['pass'] = is_dir('config/') && is_writable('config/');
    $test['config_dir']['fail'] = sprintf(_('%s directory does not exist or is not writable.'), 'SITENAME/config');
    $test['config_dir']['name'] = _('Config directory ready');
    $test['config_dir']['crit'] = true;

    $test['log_dir']['pass'] = is_dir('logs/') && is_writable('logs/');
    $test['log_dir']['fail'] = sprintf(_('%s directory does not exist or is not writable.'), 'SITENAME/logs');
    $test['log_dir']['name'] = _('Log directory ready');
    $test['log_dir']['crit'] = true;

    $test['ffmpeg']['pass'] = is_file('/usr/bin/ffmpeg');
    $test['ffmpeg']['fail'] = _('You do not appear to have ffmpeg installed. File Cabinet will not be able to create thumbnail images from uploaded videos');
    $test['ffmpeg']['name'] = _('FFMPEG installed');
    $test['ffmpeg']['crit'] = false;

    $test['mime_type']['pass'] = function_exists('finfo_open') || function_exists('mime_content_type') || !ini_get('safe_mode');
    $test['mime_type']['fail'] = _('Unable to detect MIME file type. You will need to compile finfo_open into PHP.');
    $test['mime_type']['name'] = _('MIME file type detection');
    $test['mime_type']['crit'] = true;

    if (preg_match('/-/', PHP_VERSION)) {
        $phpversion = substr(PHP_VERSION,0,strpos(PHP_VERSION, '-'));
    } else {
        $phpversion = PHP_VERSION;
    }

    $test['php_version']['pass'] = version_compare($phpversion, '5.1.0', '>=');
    $test['php_version']['fail'] = sprintf(_('Your server must run PHP version 5.1.0 or higher. You are running version %s.'), $phpversion);
    $test['php_version']['name'] = _('PHP 5 version check');
    $test['php_version']['crit'] = true;

    $memory_limit = (int)ini_get('memory_limit');

    $test['memory']['pass'] = ($memory_limit > 8);
    $test['memory']['fail'] = _('Your PHP memory limit is less than 8MB. You may encounter problems with the script at this level. We suggest raising the limit in your php.ini file or uncommenting the "ini_set(\'memory_limit\', \'10M\');" line in your config/core/config.php file after installation.');
    $test['memory']['name'] = _('Memory limit exceeded');
    $test['memory']['crit'] = false;

    $test['globals']['pass'] = !(bool)ini_get('register_globals');
    $test['globals']['fail'] = _('You have register_globals enabled. You should disable it.');
    $test['globals']['name'] = _('Register globals disabled');
    $test['globals']['crit'] = false;

    $test['magic_quotes']['pass'] = !get_magic_quotes_gpc() && !get_magic_quotes_runtime();
    $test['magic_quotes']['fail'] = _('Magic quotes is enabled. Please disable it in your php.ini file.');
    $test['magic_quotes']['name'] = _('Magic quotes disabled');
    $test['magic_quotes']['crit'] = true;

    $content[] = _('Checking your server\'s compatibility with phpWebSite...');
    $table[] = '<table cellpadding="5" style="min-width : 50%">';
    foreach  ($test as $test_section=>$val) {
        $col = array();
        $col[] = $val['name'];
        if ($val['pass']) {
            $col[] = '<span style="color : #0CD559; font-weight : bold">' . _('Passed!') . '</spam>';
        } else {
            $col[] = $val['fail'];
            if ($val['crit']) {
                $allow_install = false;
            }
        }
        $table[] = '<tr><td>' . implode('</td><td>', $col) . '</td></tr>';
    }

    $table[] = '</table>';
    $content[] = implode('', $table);

    return $allow_install;
}


function create_core_directories(&$content)
{
    require_once './core/class/File.php';
    $content[] = '<b>' . _('Copying core directories.') . '</b>';
    if (PHPWS_File::copy_directory('core/conf/', 'config/core')) {
        $content[] = _('Core configuration directory successfully copied.');
    } else {
        $content[] = sprintf(_('Unable to copy the %s directory to %s. Installation halted.'), 'core/conf/', 'config/core');
        $this->cease_install = true;
        return;
    }

    if (PHPWS_File::copy_directory('core/img/', 'images/core')) {
        $content[] = _('Core image directory successfully copied.');
    } else {
        $content[] = sprintf(_('Unable to copy the %s directory to %s. Installation halted.'), 'core/img/', 'images/core');
        $this->cease_install = true;
        return;
    }

    if (PHPWS_File::copy_directory('core/templates/', 'templates/core')) {
            $content[] = _('Core templates directory successfully copied.');
    } else {
        $content[] = sprintf(_('Unable to copy the %s directory to %s. Installation halted.'), 'core/templates/', 'templates/core');
        $this->cease_install = true;
        return;
    }

    return true;
}




?>