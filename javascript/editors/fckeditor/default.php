<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

/**
 * Setting autogrow to true forces a width of 0. This lets fckeditor have a dynamic width by
 * using its autogrow option. Setting this to false changes the editor to a specific width
 * set by this file or the module. If you want to disable autogrow entirely, comment out this
 * line in editor/custom.js. Make sure autogrow is false as well.
 * FCKConfig.Plugins.Add( 'autogrow' ) ;
 *
 */

javascript('jquery');
$autogrow = true;

$data['VALUE'] = preg_replace('@src="(./)?(images|files)/@', 'src="' . PHPWS_Core::getHomeHttp() . '\\2/', $data['VALUE']);


if (empty($data['WIDTH']) || empty($data['HEIGHT'])) {
    $data['WIDTH'] = 500;
    $data['HEIGHT'] = 300;
}

if ($autogrow) {
    $data['WIDTH'] = 0;
}


if ($data['LIMITED']) {
    $data['config'] = 'limited.js';
} else {
    $data['config'] =  'custom.php?local=' . SITE_HASH;
}

$current_theme = Layout::getCurrentTheme();
if (is_file(PHPWS_SOURCE_DIR . "themes/$current_theme/fckeditor.css")) {
    $data['current_theme'] = $current_theme;
}

if (isset($_REQUEST['module'])) {
    $data['module'] = preg_replace('/\W/', '', $_REQUEST['module']);
}

if ($_SESSION['User']->id) {
    $_SESSION['FCK_Allow'] = true;
} else {
    $_SESSION['FCK_Allow'] = false;
}

if (!defined('FCK_IMAGE_DIRECTORY')) {
    define('FCK_IMAGE_DIRECTORY', PHPWS_HOME_DIR . 'images/');
}

if (!defined('FCK_FILE_DIRECTORY')) {
    define('FCK_FILE_DIRECTORY', PHPWS_HOME_DIR . 'files/');
}

if (!defined('FCK_MEDIA_DIRECTORY')) {
    define('FCK_MEDIA_DIRECTORY', PHPWS_HOME_DIR . 'files/multimedia/');
}

$_SESSION['FCK_IMAGE_DIRECTORY'] = FCK_IMAGE_DIRECTORY;
$_SESSION['FCK_FILE_DIRECTORY'] = FCK_FILE_DIRECTORY;
$_SESSION['FCK_MEDIA_DIRECTORY'] = FCK_MEDIA_DIRECTORY;

$home_url = PHPWS_Core::getHomeHttp();

if (!defined('FCK_IMAGE_URL')) {
    define('FCK_IMAGE_URL', $home_url . 'images/');
}
if (!defined('FCK_FILE_URL')) {
    define('FCK_FILE_URL', $home_url . 'files/');
}

if (!defined('FCK_MEDIA_URL')) {
    define('FCK_MEDIA_URL', $home_url . 'files/multimedia/');
}

$_SESSION['FCK_IMAGE_URL'] = FCK_IMAGE_URL;
$_SESSION['FCK_FILE_URL'] = FCK_FILE_URL;
$_SESSION['FCK_MEDIA_URL'] = FCK_MEDIA_URL;

?>