<?php
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
define("USER_ERR_MISSING_GROUP",   -12);
define("USER_ERR_DUP_EMAIL",       -13);
define("USER_ERR_NO_EMAIL",        -14);
define("USER_ERR_BAD_EMAIL",       -15);

/* User Variable Errors */
define("USER_ERR_NO_MODULE",       -20);
define("USER_ERR_BAD_VAR",         -21);

/* Group errors */
define('USER_ERR_BAD_GROUP_NAME',  -30);
define('USER_ERR_GROUP_DNE',       -31);

/* Permission errors */
define("USER_ERR_FAIL_ON_SUBPERM", -40);

/* Authorization errors */
define("USER_ERR_MISSING_AUTH",    -50);

?>