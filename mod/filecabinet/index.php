<?php

if (!defined("PHPWS_SOURCE_DIR") ||
    !Current_User::allow("filecabinet")
    ){
  exit();
}

PHPWS_Core::initModClass("filecabinet", "Cabinet_Action.php");

Cabinet_Action::admin();

?>