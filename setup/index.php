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
PHPWS_Core::initModClass("boost", "Boost.php");

session_start();

$content = array();
$setup = & new Setup;
$title = _("phpWebSite 0.9.4") . " - ";

if (!$setup->checkSession($content) || !isset($_REQUEST['step'])){
  $title .=  "Alpha Setup";
  $setup->welcome($content);
  echo Setup::show($content, $title);
  exit();
}

if (!$setup->checkDirectories($content))
     exit(Setup::show($content, $title));

switch ($_REQUEST['step']){
 case 1:
   $title .= _("Create Config File");
   $setup->createConfig($content);
   break;

 case 2:
   $modules = explode(",", DEFAULT_MODULES);
   PHPWS_Boost::toInstall($modules);

   $title .= _("Create Core");
   $result = $setup->createCore($content);
   break;

 case 3:
   $title .= _("Install Modules");
   $result = $setup->installModules($content);
   break;
}

echo Setup::show($content, $title);
?>