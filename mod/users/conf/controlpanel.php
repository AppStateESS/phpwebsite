<?php

$link[] = array("label"       => _("User Administration"),
		 "restricted"  => TRUE,
		 "url"         => "index.php?module=users&action[admin]=main",
		 "description" => _("Lets you create and edit users and groups."),
		 "image"       => "users.png",
		 "tab"         => "admin"
		 );

$link[] = array("label"       => _("User Settings"),
		 "restricted"  => FALSE,
		 "url"         => "index.php?module=users&action[user]=main",
		 "description" => _("Lets you edit your personal user information."),
		 "image"       => "users.png",
		 "tab"         => "my_settings"
		 );


?>