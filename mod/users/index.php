<?php

$DB = new PHPWS_DB("user_permissions");
if (!isset($_REQUEST['action'])){
  return;
}

if (!class_exists("PHPWS_User")){
     PHPWS_Error::log("PHPWS_CLASS_NOT_CONSTRUCTED", "core", NULL, "<b>Class:</b> PHPWS_Users");
     return;
}

PHPWS_Core::initModClass("users", "User_Functions.php");
foreach ($_REQUEST['action'] as $area=>$command);

switch ($area){
 case "user":
   switch ($command){
   case "loginBox":
     if (!PHPWS_Core::isLastPost())
       if (!User_Functions::loginUser($_POST['block_username'], $_POST['block_password']))
	 User_Functions::badLogin();


     break;

   case "logout":
     PHPWS_Core::killAllSessions();
     PHPWS_Core::home();
     break;
   } // End of open command switch
   break;

 case "admin":
   if (!$_SESSION['User']->allow("users"))
     PHPWS_User::disallow();
   else 
     PHPWS_User::adminAction($command);
   break;
}// End area switch

?>