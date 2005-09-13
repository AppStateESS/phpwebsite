<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('blog', 'Blog.php');

if (!isset($_REQUEST['action']))
     return;

if ($_REQUEST['action'] == 'admin') {
  if (!Current_User::allow('blog')) {
    PHPWS_User::disallow(_('Tried to access admin functions in Blog module.'));
    return;
  }

  PHPWS_Core::initModClass('blog', 'Blog_Admin.php');
  Blog_Admin::main();
} else {
  Blog_User::main();
}

?>