<?php

//echo $_SESSION['User']->debug();

$DB = new PHPWS_DB("user_permissions");
if (!isset($_REQUEST['action'])){
     return;
}


if (isset($_REQUEST['User_Form']))
     PHPWS_Core::initModClass("users", "Form.php");

foreach ($_REQUEST['action'] as $area=>$command);

switch ($area){
 case "open":
   switch ($command){
   case "loginBox":
     if (!PHPWS_Core::isLastPost())
       if (!PHPWS_User::loginUser($_POST['block_username'], $_POST['block_password']))
	 echo "not logged in";

     break;

   case "logout":
     PHPWS_Core::killAllSessions();
     PHPWS_Core::home();
     break;
   } // End of open command switch
   break;

 case "closed":
   if (!$_SESSION['User']->allow("users", "user"))
     return;
   switch ($command){
   case "admin":
     PHPWS_ControlPanel::subTab(1, "stuff");
     break;

   } // End of closed command switch


   break;
}// End area switch


?>