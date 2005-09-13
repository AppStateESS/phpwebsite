<?php
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass("filecabinet", "Cabinet_Action.php");

Cabinet_Action::admin();

?>