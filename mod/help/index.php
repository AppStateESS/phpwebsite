<?php
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('help', 'Help.php');

PHPWS_Help::show_help();

?>