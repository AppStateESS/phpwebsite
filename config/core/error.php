<?php
translate("core");
// Generic 1 - 99
$errors[PHPWS_UNKNOWN]               = _("Unknown Error.");
$errors[PHPWS_FILE_NOT_FOUND]        = _("File not found.");
$errors[PHPWS_CLASS_NOT_EXIST]       = _("Class does not exist.");
$errors[PHPWS_DIR_NOT_WRITABLE]      = _("Directory is not writable.");
$errors[PHPWS_VAR_TYPE]              = _("Wrong variable type.");
$errors[PHPWS_STRICT_TEXT]           = _("Improperly formated text.");
$errors[PHPWS_INVALID_VALUE]         = _("Invalid value.");
$errors[PHPWS_NO_MODULES]            = _("No active modules installed.");
$errors[PHPWS_WRONG_TYPE]            = _("Wrong data type.");
$errors[PHPWS_DIR_NOT_SECURE]        = _("Directories are not secure.");
$errors[PHPWS_DIR_CANT_CREATE]       = _("Unable to create file directory.");
$errors[PHPWS_WRONG_CLASS]           = _("Unknown or incorrect class.");
$errors[PHPWS_UNKNOWN_MODULE]        = _("Unknown module.");

// Database.php 100 - 199
$errors[PHPWS_DB_ERROR_TABLE]        = _("Table name not set.");
$errors[PHPWS_DB_NO_VALUES]          = _("No values were set before the query");
$errors[PHPWS_DB_NO_OBJ_VARS]        = _("No variables in object.");
$errors[PHPWS_DB_BAD_OP]             = _("Not an acceptable operator.");
$errors[PHPWS_DB_BAD_TABLE_NAME]     = _("Improper table name.");
$errors[PHPWS_DB_BAD_COL_NAME]       = _("Improper column name.");
$errors[PHPWS_DB_NO_COLUMN_SET]      = _("Missing column to select.");
$errors[PHPWS_DB_NOT_OBJECT]         = _("Expecting an object variable.");
$errors[PHPWS_DB_NO_VARIABLES]       = _("Class does not contain variables.");
$errors[PHPWS_DB_NO_WHERE]           = _("Function was expecting a 'where' parameter.");
$errors[PHPWS_DB_NO_JOIN_DB]         = _("Join database does not exist.");
$errors[PHPWS_DB_NO_TABLE]           = _("Table does not exist.");

// List.php 200 - 299
$errors[PHPWS_LIST_MOD_NOT_SET]       = _("Module not set.");
$errors[PHPWS_LIST_CLASS_NOT_SET]     = _("Class not set.");
$errors[PHPWS_LIST_TABLE_NOT_SET]     = _("Table not set.");
$errors[PHPWS_LIST_COLUMNS_NOT_SET]   = _("List columns not set.");
$errors[PHPWS_LIST_NAME_NOT_SET]      = _("Name not set.");
$errors[PHPWS_LIST_OP_NOT_SET]        = _("Op not set.");
$errors[PHPWS_LIST_CLASS_NOT_EXISTS]  = _("Class does not exist.");
$errors[PHPWS_LIST_NO_ITEMS_PASSED]   = _("No items passed.");
$errors[PHPWS_LIST_DB_COL_NOT_SET]    = _("Database columns not set.");


// Form.php 300 - 399
$errors[PHPWS_FORM_BAD_NAME]          = _("You may not use '%s' as a form element name.");
$errors[PHPWS_FORM_MISSING_NAME]      = _("Unable to find element '%s'.");
$errors[PHPWS_FORM_MISSING_TYPE]      = _("Input type not set.");
$errors[PHPWS_FORM_WRONG_ELMT_TYPE]   = _("Wrong element type for procedure.");
$errors[PHPWS_FORM_NAME_IN_USE]       = _("Can't change name. Already in use.");
$errors[PHPWS_FORM_NO_ELEMENTS]       = _("No form elements have been created.");
$errors[PHPWS_FORM_NO_TEMPLATE]       = _("The submitted template is not an array.");
$errors[PHPWS_FORM_NO_FILE]           = _("File not found in _FILES array.");
$errors[PHPWS_FORM_UNKNOWN_TYPE]      = _("Unrecognized form type.");
$errors[PHPWS_FORM_INVALID_MATCH]     = _("Match for must be an array for a multiple input.");


// Item.php 400 - 499
$errors[PHPWS_ITEM_ID_TABLE]          = _("Id and table not set.");
$errors[PHPWS_ITEM_NO_RESULT]         = _("No result returned from database.");

// Module.php 500 - 599
$errors[PHPWS_NO_MOD_FOUND]           = _("Module not found in the database.");

// Error.php 600 - 699
$errors[PHPWS_NO_ERROR_FILE]          = _("No error message file found.");

// Help.php 700 - 799
$errors[PHPWS_UNMATCHED_OPTION]       = _("Help option not found in help configuration file.");

// File.php 800 - 899
$errors[PHPWS_FILE_WRONG_CONSTRUCT]   = _("PHPWS_File received an unknown construct.");
$errors[PHPWS_FILE_NONCLASS]          = _("Class not found.");
$errors[PHPWS_FILE_DELETE_DENIED]     = _("Unable to delete file.");
$errors[PHPWS_DIR_DELETE_DENIED]      = _("Unable to delete directory.");
$errors[PHPWS_DIR_NOT_WRITABLE]       = _("Directory is not writable.");
$errors[PHPWS_FILE_CANT_READ]         = _("Cannot read file.");
$errors[PHPWS_FILE_NO_FILES]          = _("_FILES array not present.");
$errors[PHPWS_FILE_DIR_NONWRITE]      = _("Unable to save file in selected directory.");
$errors[PHPWS_FILE_NO_TMP]            = _("Upload directory not set in file object.");

// Image.php 900 - 999
$errors[PHPWS_FILENAME_NOT_SET]       = _("Filename not set.");
$errors[PHPWS_DIRECTORY_NOT_SET]      = _("Directory not set.");
$errors[PHPWS_BOUND_FAILED]           = _("There was a problem loading the image file.");
$errors[PHPWS_IMG_SIZE]               = _("Image was larger than %dK size limit.");
$errors[PHPWS_IMG_WIDTH]              = _("Image width was larger than %d pixel limit.");
$errors[PHPWS_IMG_HEIGHT]             = _("Image height was larger than %d pixel limit.");
$errors[PHPWS_IMG_WRONG_TYPE]         = _("Unacceptable image type.");


// Text.php 1000-1099
$errors[PHPWS_TEXT_NOT_STRING]        = _("Function expected a string variable.");

// DBPager.php 1100 - 1199
$errors[DBPAGER_NO_TOTAL_PAGES]       = _("No pages found.");
$errors[DBPAGER_MODULE_NOT_SET]       = _("Module was not set.");
$errors[DBPAGER_TEMPLATE_NOT_SET]     = _("Template was not set.");

// Editor.php 1200 - 1299
$errors[EDITOR_MISSING_FILE]          = _("Unable to find the specified editor type.");

?>