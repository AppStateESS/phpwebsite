<?php

if (!defined("PHPWS_SOURCE_DIR") || !isset($_REQUEST['action'])){
  return NULL;
}

PHPWS_CORE::initModClass("related", "Related.php");
PHPWS_CORE::initModClass("related", "Action.php");

switch ($_REQUEST['action']){
 case "start":
   Related_Action::start();
   break;

 case "add":
   Related_Action::add();
   break;

 case "quit":
   Related_Action::quit();
   break;

 case "up":
   Related_Action::up();
   break;

 case "down":
   Related_Action::down();
   break;

 case "remove":
   Related_Action::remove();
   break;
   
 case "save":
   Related_Action::save();
   break;
  
}

?>