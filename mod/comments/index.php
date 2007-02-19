<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if ($_REQUEST['module'] != 'comments') {
  return;
}

translate('comments');
PHPWS_Core::initModClass('comments', 'Comments.php');

if (isset($_REQUEST['user_action'])) {
  Comments::userAction($_REQUEST['user_action']);
} elseif (isset($_REQUEST['admin_action']) && Current_User::authorized('comments')) {
  Comments::adminAction($_REQUEST['admin_action']);
} elseif (isset($_REQUEST['cm_id'])) {
    Comments::userAction('view_comment');
} else {
    PHPWS_Core::errorPage('404');
 }
translate();
?>