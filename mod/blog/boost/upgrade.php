<?php
if (!Current_User::isDeity())
  Current_User::disallow();

  function blog_upgrade(&$content, $currentVersion){
    if ($currentVersion < "0.0.2"){
      $users = & new PHPWS_Module("users");
      $blog = & new PHPWS_Module("blog");
      PHPWS_Boost::registerModToMod($users, $blog, $content);

      $content[] = "+ Added permissions to Blog";
    }

    return TRUE;
  }

?>