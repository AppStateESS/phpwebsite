<?php

if ($_REQUEST['module'] != "layout" || !isset($_REQUEST['action'])){
  layoutDefault();
}

PHPWS_Core::initModClass("layout", "LayoutAdmin.php");

switch ($_REQUEST['action']){
 case "admin":
   Layout_Admin::admin();
   break;
} // END action switch


function layoutDefault(){
  exit(header("location:./../../index.php"));
}
?>
