<?php
translate("core");
// Generic
$errors[PHPWS_UNKNOWN]               = _("Unknown Error");
$errors[PHPWS_FILE_NOT_FOUND]        = _("File not found");
$errors[PHPWS_CLASS_NOT_EXIST]       = _("Class does not exist");
$errors[PHPWS_DIR_NOT_WRITABLE]      = _("Directory is not writable");
$errors[PHPWS_VAR_TYPE]              = _("Wrong variable type.");
$errors[PHPWS_STRICT_TEXT]           = _("Improperly formated text.");
$errors[PHPWS_INVALID_VALUE]         = _("Invalid value");

// Database.php 100 - 199
$errors[PHPWS_DB_ERROR_TABLE]        = _("Table name not set");
$errors[PHPWS_DB_NO_VALUES]          = _("No values were set before the query");

// List.php 200 - 299
$errors[PHPWS_LIST_MOD_NOT_SET]      = _("Module not set");
$errors[PHPWS_LIST_CLASS_NOT_SET]    = _("Class not set");
$errors[PHPWS_LIST_TABLE_NOT_SET]    = _("Table not set");
$errors[PHPWS_LIST_COLUMNS_NOT_SET]  = _("Columns not set");
$errors[PHPWS_LIST_NAME_NOT_SET]     = _("Name not set");
$errors[PHPWS_LIST_OP_NOT_SET]       = _("Op not set");
$errors[PHPWS_LIST_NO_ITEMS_PASSED]  = _("No items passed");

// Form.php 300 - 399
$errors[PHPWS_FORM_BAD_NAME]         = _("You may not use '[var1]' as a form element name");
$errors[PHPWS_FORM_MISSING_NAME]     = _("Unable to find element '[var1]'");
$errors[PHPWS_FORM_MISSING_TYPE]     = _("Input type not set");
$errors[PHPWS_FORM_WRONG_ELMT_TYPE]  = _("Wrong element type for procedure");
$errors[PHPWS_FORM_NAME_IN_USE]      = _("Can't change name. Already in use.");
$errors[PHPWS_FORM_NO_ELEMENTS]      = _("No form elements have been created.");
$errors[PHPWS_FORM_NO_TEMPLATE]      = _("The submitted template is not an array");
$errors[PHPWS_FORM_NO_FILE]          = _("<b>[var1]</b> not found in _FILES array");
$errors[PHPWS_FORM_IMG_TOO_BIG]      = _("Submitted image was larger than [var1]KB limit");
$errors[PHPWS_FORM_WIDTH_TOO_BIG]    = _("Submitted image width was larger than [var1] pixel limit.");
$errors[PHPWS_FORM_HEIGHT_TOO_BIG]   = _("Submitted image height was larger than [var1] pixel limit.");
$errors[PHPWS_FORM_UNKNOWN_TYPE]     = _("Unrecognized form type.");
$errors[PHPWS_FORM_INVALID_MATCH]    = _("Match for must be an array for a multiple input.");


// Item.php 400 - 499
$errors[PHPWS_ITEM_ID_TABLE]         = _("Id and table not set.");
$errors[PHPWS_ITEM_NO_RESULT]        = _("No result returned from database.");

// Module.php 500 - 599
$errors[PHPWS_NO_MOD_FOUND]          = _("Module not found in the database.");

?>