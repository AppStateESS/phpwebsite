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
define("USERNAME_LENGTH", 4);
define("GROUPNAME_LENGTH", 4);

/** Authorization Mode
 * Leave this alone
 */
define("LOCAL_AUTHORIZATION", 1);
define("GLOBAL_AUTHORIZATION", 2);

?>