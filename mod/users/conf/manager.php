<?php

/**
 * @version $Id$
 * @author  Matthew McNaney <matt at tux dot appstate dot edu>
 */

/* Labels */

$lists        = array("users"  => NULL);

$tables       = array("users"  => "users");

$templates    = array("users"  => "mod/users/template/manager.tpl");

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

?>