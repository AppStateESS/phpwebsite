<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (isset($_REQUEST['site_map'])) {
    Menu::siteMap();
} elseif(Current_User::allow('menu')) {
    Menu::admin();
} else {
    \phpws\PHPWS_Core::errorPage('404');
}
