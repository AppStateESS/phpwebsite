<?php

$DB = new PHPWS_DB("users");
$DB->addWhere("username", strtolower($username));
$DB->addWhere("password", md5($password));
$DB->addColumn("id");
$ID = $DB->select("one");
if (!isset($ID))
     $logged = FALSE;
     else
     $logged = TRUE;

?>