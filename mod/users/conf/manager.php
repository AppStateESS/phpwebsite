<?php

/**
 * @version $Id$
 * @author  Matthew McNaney <matt at tux dot appstate dot edu>
 */

/* Labels */

$lists        = array("users"  => NULL);

$tables       = array("users"  => "users");

$templates    = array("users"  => "manager");

$activeValues = array(0=>"Active", 1=>"Inactive");
$approvedValues = array(0=>"True", 1=>"False");
$deityValues    = array(0=>"Deity", 1=>"Mortal");

$usersColumns = array("id"      =>"ID",
		      "username"=>"Username",
		      "deity"   =>"Deity",
		      "approved"=>"Approved",
		      "active"  =>"Active",
		      "created" =>"Created",
		      "updated" =>NULL,
		      "password"=>NULL
		      );

$usersActions = array("delete"=>"Delete");

$usersPermissions = array();

$usersPaging = array("op"=>"PHPWS_Users_op=testing",
			   "limit"=>10,
			   "section"=>1,
			   "limits"=>array(5,10,20,50),
			   "back"=>"&#60;&#60;",
			   "forward"=>"&#62;&#62;");

?>