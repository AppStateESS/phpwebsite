<?php

if (!Current_User::authorized('block')) {
  Current_User::disallow();
  return;
}

PHPWS_Core::initModClass('block', 'Block_Admin.php');

Block_Admin::action();
?>