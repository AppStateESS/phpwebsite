<?php

if (!defined("PHPWS_SOURCE_DIR") ||
    !isset($_REQUEST['action'])  ||
    !Current_User::allow("filecabinet")
    ){
  header("location:../../index.php");
  exit();
}

PHPWS_Core::initModClass("filecabinet", "Cabinet_Action.php");

Cabinet_Action::admin();

?>