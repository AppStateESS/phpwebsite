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
    \core\Core::initModClass('rss', 'Admin.php');
    RSS_Admin::main();
} elseif (isset($_GET['mod_title'])) {
    \core\Core::initModClass('rss', 'RSS.php');
    RSS::viewChannel($_GET['mod_title']);
} elseif (isset($_GET['id'])) {
    \core\Core::initModClass('rss', 'RSS.php');
    RSS::viewChannel($_GET['id']);
} else {
    \core\Core::errorPage('404');
}

?>