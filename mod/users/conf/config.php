<?php

define("BAD_PASSWORDS",
       serialize(array("pass",
		       "password",
		       "pssword",
		       "asdfg",
		       "qwerty",
		       "phpwebsite",
		       "admin",
		       "phpws",
		       "asdlkj"		       
		       )
		 )
       );


define("DEFAULT_ITEMNAME", "common");
define("DEFAULT_USER_MENU", "new_user");

define("FULL_PERMISSION",    2);
define("PARTIAL_PERMISSION", 1);
define("NO_PERMISSION",      0);

define("PASSWORD_LENGTH", 5);

/* Errors */
define("USER_ERROR",               -1);
define("USER_ERR_DUP_USERNAME",    -2);
define("USER_ERR_DUP_GROUPNAME",   -3);
define("USER_ERR_PERM_TABLE",      -4);
define("USER_ERR_PERM_MISS",       -5);
define("USER_ERR_PERM_FILE",       -6);
define("USER_ERR_BAD_USERNAME",    -7);
define("USER_ERR_PASSWORD_MATCH",  -8);
define("USER_ERR_PASSWORD_LENGTH", -9);
define("USER_ERR_PASSWORD_EASY",   -10);
define("USER_ERR_USER_NOT_SAVED",  -11);

/* User Variable Errors */
define("USER_ERR_NO_MODULE",     -20);
define("USER_ERR_BAD_VAR",       -21);

/* Demographics errors */
define('USER_ERR_LABEL_NOT_FOUND', -31); 
define('USER_ERR_UNKNOWN_INPUT',   -32); 



?>