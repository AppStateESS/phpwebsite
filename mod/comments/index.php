<?php

if ($_REQUEST['module'] != 'comments') {
  return;
}

PHPWS_Core::initModClass('comments', 'Comments.php');

if (isset($_REQUEST['user_action'])) {
  Comments::userAction($_REQUEST['user_action']);
} elseif (isset($_REQUEST['admin_action'])&& Current_User::authorized('comments')) {
  Comments::userAction($_REQUEST['admin_action']);
}

?>