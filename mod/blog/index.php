<?php

PHPWS_Core::initModClass("blog", "Blog.php");

if ($_REQUEST['action'] == "admin") {
  if (!Current_User::allow("blog")) {
    PHPWS_User::disallow(_("Tried to access admin functions in Blog module."));
    return;
  }

  PHPWS_Core::initModClass("blog", "Blog_Admin.php");
  Blog_Admin::main();
} elseif ($_REQUEST['action'] == "view") {
  $blog = & new Blog($_REQUEST['id']);
  Layout::add($blog->view(TRUE, FALSE));
}

?>