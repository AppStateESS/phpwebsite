<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if (!isset($_SESSION['Clipboard'])) {
  Clipboard::init();
}

Clipboard::action();

?>