<?php
chdir("../");
if (isset($_REQUEST['step']) && $_REQUEST['step'] > 1)
  require_once "./config/core/config.php";
else
  require_once "./setup/preconfig.php";

require_once "./core/class/Init.php";
include_once "./setup/config.php";
require_once "./setup/class/Setup.php";

PHPWS_Core::initCoreClass("Form.php");
PHPWS_Core::initCoreClass("Text.php");
PHPWS_Core::initCoreClass("Template.php");

session_start();

$content = array();
$setup = & new Setup;
$title = _("phpWebSite 0.9.4 Alpha Setup");

if (!$setup->checkSession($content) || !isset($_REQUEST['step'])){
  $setup->welcome($content);
  echo Setup::show($content, $title);
  exit();
}

$setup->checkDirectories($content);

switch ($_REQUEST['step']){
 case 1:
   $title = _("phpWebSite 0.9.4 - Create Config File");
   $setup->createConfig($content);
   break;

 case 2:
   $title = _("phpWebSite 0.9.4 - Create Core");
   $setup->createCore($content);
   break;
}

echo Setup::show($content, $title);
?>