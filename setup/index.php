<?php
chdir("../");

include "class/Init.php";
include "setup/config.php";
include "setup/class/Setup.php";


PHPWS_Core::initCoreClass("Form.php");
PHPWS_Core::initCoreClass("Text.php");
PHPWS_Core::initCoreClass("Template.php");

session_start();

$content = array();
$setup = & new Setup;

if (!$setup->checkSession($content) || !isset($_REQUEST['step']))
  $setup->welcome($content);


$setup->checkDirectories($content);

switch ($_REQUEST['step']){
 case 1:
   $setup->createConfig($content);
   break;

 case 2:
   $content[] = _("");
   break;
}




echo Setup::show($content);
?>