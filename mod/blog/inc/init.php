<?php

PHPWS_Core::configRequireOnce("blog", "config.php");

PHPWS_Core::initModClass("blog", "Blog.php");
PHPWS_Core::initModClass("blog", "Blog_User.php");

?>