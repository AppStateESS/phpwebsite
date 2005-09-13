<?php
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('categories', 'Action.php');
PHPWS_Core::initModClass('categories', 'Categories.php');

if ($_REQUEST['action'] == 'admin'){
  Categories_Action::admin();
}
else {
  Categories_Action::user();
}

?>