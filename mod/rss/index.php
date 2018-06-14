<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (isset($_GET['feed'])) {
    Layout::add(RSS::viewFeed($_GET['feed']));
} elseif (( isset($_REQUEST['command']) || isset($_REQUEST['tab']) ) && Current_User::allow('rss')) {
    \phpws\PHPWS_Core::initModClass('rss', 'Admin.php');
    RSS_Admin::main();
} elseif (isset($_GET['mod_title'])) {
    \phpws\PHPWS_Core::initModClass('rss', 'RSS.php');
    RSS::viewChannel($_GET['mod_title']);
} elseif (isset($_GET['id'])) {
    \phpws\PHPWS_Core::initModClass('rss', 'RSS.php');
    RSS::viewChannel($_GET['id']);
} else {
    \phpws\PHPWS_Core::errorPage('404');
}
