<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (isset($_REQUEST['wp_user'])) {
    PHPWS_Core::initModClass('webpage', 'User.php');
    Webpage_User::main();
} elseif(isset($_REQUEST['wp_admin'])) {
    PHPWS_Core::initModClass('webpage', 'Admin.php');
    Webpage_Admin::main();
} elseif (isset($_REQUEST['id'])) {
    PHPWS_Core::initModClass('webpage', 'User.php');
    Webpage_User::main('view');
} elseif (Current_User::allow('webpage')) {
    PHPWS_Core::initModClass('webpage', 'Admin.php');
    Webpage_Admin::main();
} else {
    PHPWS_Core::errorPage('404');
}

?>