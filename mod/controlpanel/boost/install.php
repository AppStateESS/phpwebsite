<?php

if (!$_SESSION["OBJ_user"]->isDeity()){
  header("Location: index.php");
  exit();
}

if($GLOBALS["core"]->sqlImport(PHPWS_SOURCE_DIR . "mod/controlpanel/boost/install.sql", TRUE)) {
  $content .= "All Control Panel tables successfully written.<br />";
  $status = 1;
} else {
  $content .= "There was a problem writing to the database.<br />";
}

?>
