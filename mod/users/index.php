<?php

if (!isset($_REQUEST['action'])) return;

if (!class_exists("PHPWS_User")){
     PHPWS_Error::log("PHPWS_CLASS_NOT_CONSTRUCTED", "core", NULL, "<b>Class:</b> PHPWS_Users");
     return;
}

PHPWS_Core::initModClass("users", "Action.php");

foreach ($_REQUEST['action'] as $area=>$command);

switch ($area){
 case "user":
   User_Action::userAction($command);
   break;

 case "admin":
   User_Action::adminAction($command);
   break;
}// End area switch

?>