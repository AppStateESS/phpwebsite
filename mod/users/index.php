<?php

$DB = new PHPWS_DB("user_permissions");
if (!isset($_REQUEST['action'])){
  return;
}

foreach ($_REQUEST['action'] as $area=>$command);

switch ($area){
 case "user":
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

 case "admin":
   if (!$_SESSION['User']->allow("users"))
     PHPWS_User::disallow();
   else 
     PHPWS_User::adminAction($command);
   break;
}// End area switch

?>