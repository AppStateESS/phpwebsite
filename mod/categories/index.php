<?php

if (!defined("PHPWS_SOURCE_DIR")){
  header("location:../../index.php");
  exit();
}
PHPWS_Core::initModClass("categories", "Action.php");
PHPWS_Core::initModClass("categories", "Categories.php");

if ($_REQUEST['action'] == 'admin'){
  Categories_Action::admin();
}
elseif ($_REQUEST['action'] == 'user'){
  Categories_Action::user();
}

?>