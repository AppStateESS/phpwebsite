<?php

if ($_REQUEST['module'] != "layout" || !isset($_REQUEST['action'])){
  layoutDefault();
}

PHPWS_Core::initModClass("layout", "LayoutAdmin.php");

foreach ($_REQUEST['action'] as $section => $action);

switch ($section){
 case "admin":
   Layout::addStyle("layout");
  switch ($action){
  case "main":
    Layout_Admin::main();
    break;

  default:
    $content = array();
    Layout_Admin::action($action, $content);
    Layout_Admin::adminPanel(implode("", $content));
    break;
 } // END action switch
  break;
} // END section switch


function layoutDefault(){
  exit(header("location:./../../index.php"));
}
?>
