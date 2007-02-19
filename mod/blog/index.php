<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('blog', 'Blog.php');
translate('blog');
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'admin' &&
    Current_User::allow('blog')) {
    PHPWS_Core::initModClass('blog', 'Blog_Admin.php');
    Blog_Admin::main();
} else {
    Blog_User::main();
}
translate();
?>