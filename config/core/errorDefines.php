<?php
/* Error defines */

/************* Generic  *****************/
define("PHPWS_UNKNOWN",               -1);
define("PHPWS_FILE_NOT_FOUND",        -2);
define("PHPWS_CLASS_NOT_EXIST",       -3);
define("PHPWS_DIR_NOT_WRITABLE",      -4);
define("PHPWS_VAR_TYPE",              -5);
define("PHPWS_STRICT_TEXT",           -6);
define("PHPWS_INVALID_VALUE",         -7);
define("PHPWS_NO_MODULES",            -8);
define("PHPWS_WRONG_TYPE",            -9);
define("PHPWS_DIR_NOT_SECURE",       -10);

/*********** Database.php ***************/

define("PHPWS_DB_ERROR_TABLE",        -100);
define("PHPWS_DB_NO_VALUES",          -101);
define("PHPWS_DB_NO_OBJ_VARS",        -102);
define("PHPWS_DB_BAD_OP",             -103);

/************* List.php *****************/
define("PHPWS_LIST_MOD_NOT_SET",      -200);
define("PHPWS_LIST_CLASS_NOT_SET",    -201);
define("PHPWS_LIST_TABLE_NOT_SET",    -202);
define("PHPWS_LIST_COLUMNS_NOT_SET",  -203);
define("PHPWS_LIST_NAME_NOT_SET",     -204);
define("PHPWS_LIST_OP_NOT_SET",       -205);
define("PHPWS_LIST_CLASS_NOT_EXISTS", -206);
define("PHPWS_LIST_NO_ITEMS_PASSED",  -207);
define("PHPWS_LIST_DB_COL_NOT_SET",   -208);

/************* Form.php *****************/
define("PHPWS_FORM_BAD_NAME",         -301);
define("PHPWS_FORM_MISSING_NAME",     -302);
define("PHPWS_FORM_WRONG_ELMT_TYPE",  -303);
define("PHPWS_FORM_NAME_IN_USE",      -304);
define("PHPWS_FORM_MISSING_TYPE",     -305);
define("PHPWS_FORM_NO_ELEMENTS",      -306);
define("PHPWS_FORM_NO_TEMPLATE",      -307);
define("PHPWS_FORM_NO_FILE",          -308);
define("PHPWS_FORM_IMG_TOO_BIG",      -309);
define("PHPWS_FORM_WIDTH_TOO_BIG",    -310);
define("PHPWS_FORM_HEIGHT_TOO_BIG",   -311);
define("PHPWS_FORM_UNKNOWN_TYPE",     -312);
define("PHPWS_FORM_INVALID_MATCH",    -313);

define("PHPWS_FORM_ERROR_FILE_POST",  -320);


/*************** Item.php *****************/
define("PHPWS_ITEM_ID_TABLE",         -400);
define("PHPWS_ITEM_NO_RESULT",        -401);


/*************** Module.php ***************/
define("PHPWS_NO_MOD_FOUND",          -500);   

/*************** Error.php ****************/
define("PHPWS_NO_ERROR_FILE",         -600);

/*************** Help.php  ****************/
define("PHPWS_UNMATCHED_OPTION",      -700);


/*************** File.php ****************/
define("PHPWS_FILE_DELETE_DENIED",    -800);
define("PHPWS_DIR_DELETE_DENIED",    -800);

?>