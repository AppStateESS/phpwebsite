<?php
// copy this file in your phpwebsite root directory as
// test.php. Use only for development.

header('Content-Type: text/html; charset=UTF-8');
include 'config/core/config.php';
require_once 'core/class/Init.php';

PHPWS_Core::initCoreClass('Database.php');
PHPWS_Core::initCoreClass('Form.php');
PHPWS_Core::initCoreClass('Template.php');

/****************************************************/

echo 'Hi! I\'m a test file!';

/******************************************************/
?>

