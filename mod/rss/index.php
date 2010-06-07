<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if ( ( isset($_REQUEST['command']) || isset($_REQUEST['tab']) ) && Current_User::allow('rss')) {
    Core\Core::initModClass('rss', 'Admin.php');
    RSS_Admin::main();
} elseif (isset($_GET['mod_title'])) {
    Core\Core::initModClass('rss', 'RSS.php');
    RSS::viewChannel($_GET['mod_title']);
} elseif (isset($_GET['id'])) {
    Core\Core::initModClass('rss', 'RSS.php');
    RSS::viewChannel($_GET['id']);
} else {
    Core\Core::errorPage('404');
}

?>