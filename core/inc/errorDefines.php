<?php
/* Error defines */

/************* Generic  *****************/
define('PHPWS_UNKNOWN',               -1);
define('PHPWS_FILE_NOT_FOUND',        -2);
define('PHPWS_CLASS_NOT_EXIST',       -3);
define('PHPWS_DIR_NOT_WRITABLE',      -4);
define('PHPWS_VAR_TYPE',              -5);
define('PHPWS_STRICT_TEXT',           -6);
define('PHPWS_INVALID_VALUE',         -7);
define('PHPWS_NO_MODULES',            -8);
define('PHPWS_WRONG_TYPE',            -9);
define('PHPWS_DIR_NOT_SECURE',       -10);
define('PHPWS_DIR_CANT_CREATE',      -11);
define('PHPWS_WRONG_CLASS',          -12);
define('PHPWS_UNKNOWN_MODULE',       -13);
define('PHPWS_CLASS_VARS',           -14);
define('PHPWS_NO_FUNCTION',          -15);
define('PHPWS_HUB_IDENTITY',         -16);

/*********** Database.php ***************/

define('PHPWS_DB_ERROR_TABLE',        -100);
define('PHPWS_DB_NO_VALUES',          -101);
define('PHPWS_DB_NO_OBJ_VARS',        -102);
define('PHPWS_DB_BAD_OP',             -103);
define('PHPWS_DB_BAD_TABLE_NAME',     -104);
define('PHPWS_DB_BAD_COL_NAME',       -104);
define('PHPWS_DB_NO_COLUMN_SET',      -105);
define('PHPWS_DB_NOT_OBJECT',         -106);
define('PHPWS_DB_NO_VARIABLES',       -107);
define('PHPWS_DB_NO_WHERE',           -108);
define('PHPWS_DB_NO_JOIN_DB',         -109);
define('PHPWS_DB_NO_TABLE',           -110);
define('PHPWS_DB_NO_ID',              -111);
define('PHPWS_DB_EMPTY_SELECT',       -112);
define('PHPWS_DB_IMPORT_FAILED',      -113);


/************* List.php *****************/
define('PHPWS_LIST_MOD_NOT_SET',      -200);
define('PHPWS_LIST_CLASS_NOT_SET',    -201);
define('PHPWS_LIST_TABLE_NOT_SET',    -202);
define('PHPWS_LIST_COLUMNS_NOT_SET',  -203);
define('PHPWS_LIST_NAME_NOT_SET',     -204);
define('PHPWS_LIST_OP_NOT_SET',       -205);
define('PHPWS_LIST_CLASS_NOT_EXISTS', -206);
define('PHPWS_LIST_NO_ITEMS_PASSED',  -207);
define('PHPWS_LIST_DB_COL_NOT_SET',   -208);

/************* Form.php *****************/
define('PHPWS_FORM_BAD_NAME',         -301);
define('PHPWS_FORM_MISSING_NAME',     -302);
define('PHPWS_FORM_WRONG_ELMT_TYPE',  -303);
define('PHPWS_FORM_NAME_IN_USE',      -304);
define('PHPWS_FORM_MISSING_TYPE',     -305);
define('PHPWS_FORM_NO_ELEMENTS',      -306);
define('PHPWS_FORM_NO_TEMPLATE',      -307);
define('PHPWS_FORM_NO_FILE',          -308);
define('PHPWS_FORM_UNKNOWN_TYPE',     -309);
define('PHPWS_FORM_INVALID_MATCH',    -310);

define('PHPWS_FORM_ERROR_FILE_POST',  -320);


/*************** Item.php *****************/
define('PHPWS_ITEM_ID_TABLE',         -400);
define('PHPWS_ITEM_NO_RESULT',        -401);


/*************** Module.php ***************/
define('PHPWS_NO_MOD_FOUND',          -500);   

/*************** Error.php ****************/
define('PHPWS_NO_ERROR_FILE',         -600);
define('PHPWS_NO_MODULE',             -601);

/*************** Help.php  ****************/
define('PHPWS_UNMATCHED_OPTION',      -700);


/*************** File.php ****************/
define('PHPWS_FILE_WRONG_CONSTRUCT',  -800);
define('PHPWS_FILE_NONCLASS',         -801);
define('PHPWS_FILE_DELETE_DENIED',    -802);
define('PHPWS_DIR_DELETE_DENIED',     -803);
define('PHPWS_FILE_NO_FILES',         -804);
define('PHPWS_FILE_CANT_READ',        -805);
define('PHPWS_FILE_DIR_NONWRITE',     -806);
define('PHPWS_FILE_NO_TMP',           -807);
define('PHPWS_FILE_SIZE',             -808);
define('PHPWS_GD_ERROR',              -809);
define('PHPWS_FILE_NOT_WRITABLE',     -810);
define('PHPWS_FILE_NO_COPY',          -811);

/*************** Text.php *****************/
define('PHPWS_TEXT_NOT_STRING',       -1000);

/************** DBPager.php ***************/
define('DBPAGER_NO_TOTAL_PAGES',      -1100);
define('DBPAGER_MODULE_NOT_SET',      -1101);
define('DBPAGER_TEMPLATE_NOT_SET',    -1102);
define('DBPAGER_NO_TABLE',            -1103);
define('DBPAGER_NO_METHOD',           -1104);

/************** Editor.php ****************/
define('EDITOR_MISSING_FILE',         -1200);

/************** Settings.php **************/
define('SETTINGS_MISSING_FILE',       -1300);

/**************  Key.php  *****************/
define('KEY_NOT_FOUND',               -1400);
define('KEY_PERM_COLUMN_MISSING',     -1401);
define('KEY_UNREG_FILE_MISSING',      -1402);
define('KEY_UNREG_FUNC_MISSING',      -1403);
define('KEY_RESTRICT_NO_TABLE',       -1404);
define('KEY_DUPLICATE',               -1405);

/*********** Cookie.php ******************/
define('COOKIE_SET_FAILED',           -1600);
?>