<?php

$DB = new PHPWS_DB("users");
$DB->addWhere("username", strtolower($username));
$DB->addWhere("password", md5($password));
$DB->addColumn("id");
$id = $DB->select("one");
if (!isset($id))
     $logged = FALSE;
     else
     $logged = TRUE;

?>