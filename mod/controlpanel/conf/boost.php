<?php

$mod_title = "controlpanel";
$mod_pname = "Control Panel";
$mod_directory = "controlpanel";
$mod_filename = "index.php";
$mod_icon = "";
$admin_mod = 0;
$admin_op = "";
$allow_view = array("controlpanel"=>1);
$priority = 50;
$active = "on";
$version = "0.80";

$mod_class_files = array("ControlPanel.php",
			 "Tab.php",
			 "Link.php");

$mod_sessions = array("PHPWS_ControlPanel");

?>