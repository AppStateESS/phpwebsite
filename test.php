<?php


define("PHPWS_SOURCE_DIR", "./");

include("conf/pear_config.php");

include "PEAR.php";
require_once "class/Core.php";
require_once "class/Form.php";
PHPWS_Core::initModClass("layout", "Layout.php");
PHPWS_Core::initModClass("users", "Users.php");
PHPWS_Core::initModClass("users", "Permission.php");

PHPWS_Core::initModClass("layout", "Initialize.php");
PHPWS_Core::initModClass("language", "Translate.php");
/*
PHPWS_Core::initModClass("controlpanel", "Tab.php");
PHPWS_Core::initModClass("controlpanel", "ControlPanel.php");
*/
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
<head>

<meta http-equiv=\"content-type\" content=\"text/html; charset=ISO-8859-1\" />
<link rel=\"stylesheet\" href=\"http://localhost/phpwebsite094/themes/Default/style.css\" type=\"text/css\" />
</head>
<body>";

/****************************************************/

$user = new PHPWS_User(2);

/*
$rights = array('add_edit_users'=>1, 'delete_users'=>1, 'add_edit_groups'=>1, 'delete_groups'=>1);

PHPWS_User_Permission::setPermissions(1, "users", "user", $rights);
*/


if ($user->allow("users", "user", 'delete_users', 1))
     echo "allowed";
     else
     echo "not allowed";

echo phpws_debug::testobject($user);

/******************************************************/

echo "<hr />
</body>
</html>";

?>