<?php

// Generic 1 - 99
$errors[PHPWS_UNKNOWN]               = _('Unknown Error.');
$errors[PHPWS_FILE_NOT_FOUND]        = _('File not found.');
$errors[PHPWS_CLASS_NOT_EXIST]       = _('Class does not exist.');
$errors[PHPWS_DIR_NOT_WRITABLE]      = _('Directory is not writable.');
$errors[PHPWS_VAR_TYPE]              = _('Wrong variable type.');
$errors[PHPWS_STRICT_TEXT]           = _('Improperly formated text.');
$errors[PHPWS_INVALID_VALUE]         = _('Invalid value.');
$errors[PHPWS_NO_MODULES]            = _('No active modules installed.');
$errors[PHPWS_WRONG_TYPE]            = _('Wrong data type.');
$errors[PHPWS_DIR_NOT_SECURE]        = _('Directories are not secure.');
$errors[PHPWS_DIR_CANT_CREATE]       = _('Unable to create file directory.');
$errors[PHPWS_WRONG_CLASS]           = _('Unknown or incorrect class.');
$errors[PHPWS_UNKNOWN_MODULE]        = _('Unknown module.');
$errors[PHPWS_CLASS_VARS]            = _('Unable to derive class variables.');
$errors[PHPWS_NO_FUNCTION]           = _('Function name not found.');
$errors[PHPWS_HUB_IDENTITY]          = _('Unable to verify source directory. Check PHPWS_SOURCE_DIR.');

// Database.php 100 - 199
$errors[DB_ERROR_TABLE]        = _('Table name not set.');
$errors[DB_NO_VALUES]          = _('No values were set before the query');
$errors[DB_NO_OBJ_VARS]        = _('No variables in object.');
$errors[DB_BAD_OP]             = _('Not an acceptable operator.');
$errors[DB_BAD_TABLE_NAME]     = _('Improper table name.');
$errors[DB_BAD_COL_NAME]       = _('Improper column name.');
$errors[DB_NO_COLUMN_SET]      = _('Missing column to select.');
$errors[DB_NOT_OBJECT]         = _('Expecting an object variable.');
$errors[DB_NO_VARIABLES]       = _('Class does not contain variables.');
$errors[DB_NO_WHERE]           = _('Function was expecting a "where" parameter.');
$errors[DB_NO_JOIN_DB]         = _('Join database does not exist.');
$errors[DB_NO_TABLE]           = _('Table does not exist.');
$errors[DB_NO_ID]              = _('loadObject expected the object to have an id or where clause.');
$errors[DB_EMPTY_SELECT]       = _('Select returned an empty result.');
$errors[DB_IMPORT_FAILED]      = _('Database import failed.');


// List.php 200 - 299
$errors[PHPWS_LIST_MOD_NOT_SET]       = _('Module not set.');
$errors[PHPWS_LIST_CLASS_NOT_SET]     = _('Class not set.');
$errors[PHPWS_LIST_TABLE_NOT_SET]     = _('Table not set.');
$errors[PHPWS_LIST_COLUMNS_NOT_SET]   = _('List columns not set.');
$errors[PHPWS_LIST_NAME_NOT_SET]      = _('Name not set.');
$errors[PHPWS_LIST_OP_NOT_SET]        = _('Op not set.');
$errors[PHPWS_LIST_CLASS_NOT_EXISTS]  = _('Class does not exist.');
$errors[PHPWS_LIST_NO_ITEMS_PASSED]   = _('No items passed.');
$errors[PHPWS_LIST_DB_COL_NOT_SET]    = _('Database columns not set.');


// Form.php 300 - 399
$errors[PHPWS_FORM_BAD_NAME]          = _('You may not use "%s" as a form element name.');
$errors[PHPWS_FORM_MISSING_NAME]      = _('Unable to find element "%s".');
$errors[PHPWS_FORM_MISSING_TYPE]      = _('Input type not set.');
$errors[PHPWS_FORM_WRONG_ELMT_TYPE]   = _('Wrong element type for procedure.');
$errors[PHPWS_FORM_NAME_IN_USE]       = _('Can not change name. Already in use.');
$errors[PHPWS_FORM_NO_ELEMENTS]       = _('No form elements have been created.');
$errors[PHPWS_FORM_NO_TEMPLATE]       = _('The submitted template is not an array.');
$errors[PHPWS_FORM_NO_FILE]           = _('File not found in _FILES array.');
$errors[PHPWS_FORM_UNKNOWN_TYPE]      = _('Unrecognized form type.');
$errors[PHPWS_FORM_INVALID_MATCH]     = _('Match for must be an array for a multiple input.');


// Item.php 400 - 499
$errors[PHPWS_ITEM_ID_TABLE]          = _('Id and table not set.');
$errors[PHPWS_ITEM_NO_RESULT]         = _('No result returned from database.');

// Module.php 500 - 599
$errors[PHPWS_NO_MOD_FOUND]           = _('Module not found.');

// Error.php 600 - 699
$errors[PHPWS_NO_ERROR_FILE]          = _('No error message file found.');
$errors[PHPWS_NO_MODULE]              = _('Blank module title sent to get function.');

// Help.php 700 - 799
$errors[PHPWS_UNMATCHED_OPTION]       = _('Help option not found in help configuration file.');

// File.php 800 - 899
$errors[FILE_WRONG_CONSTRUCT]   = _('File received an unknown construct.');
$errors[FILE_NONCLASS]          = _('Class not found.');
$errors[FILE_DELETE_DENIED]     = _('Unable to delete file.');
$errors[DIR_DELETE_DENIED]      = _('Unable to delete directory.');
$errors[DIR_NOT_WRITABLE]       = _('Directory is not writable.');
$errors[FILE_CANT_READ]         = _('Cannot read file.');
$errors[FILE_NO_FILES]          = _('Variable name not found in_FILES array.');
$errors[FILE_DIR_NONWRITE]      = _('Unable to save file in selected directory.');
$errors[FILE_NO_TMP]            = _('Upload directory not set in file object.');
$errors[FILE_SIZE]              = sprintf(_('Upload file size is larger than the server %s maximum.'), ini_get('post_max_size'));
$errors[GD_ERROR]               = _('GD image libraries do not support this image type.');
$errors[FILE_NOT_WRITABLE]      = _('File not writable.');
$errors[FILE_NO_COPY]           = _('Unable to copy file.');

// Text.php 1000-1099
$errors[PHPWS_TEXT_NOT_STRING]        = _('Function expected a string variable.');

// DBPager.php 1100 - 1199
$errors[DBPAGER_NO_TOTAL_PAGES]       = _('No pages found.');
$errors[DBPAGER_MODULE_NOT_SET]       = _('Module was not set.');
$errors[DBPAGER_TEMPLATE_NOT_SET]     = _('Template was not set.');
$errors[DBPAGER_NO_TABLE]             = _('Table is blank');
$errors[DBPAGER_NO_METHOD]            = _('Method does not exist in specified class.');
$errors[DBPAGER_NO_CLASS]             = _('Class does not exist.');

// Editor.php 1200 - 1299
$errors[EDITOR_MISSING_FILE]          = _('Unable to find the specified editor type.');

// Settings.php 1300 - 1399
$errors[SETTINGS_MISSING_FILE]        = _('Unable to find your module\'s settings.php file.');

// Key.php 1400 - 1499
$errors[KEY_NOT_FOUND]                = _('Key not found.');
$errors[KEY_PERM_COLUMN_MISSING]      = _('Edit permission column does not exist.');
$errors[KEY_UNREG_FILE_MISSING]       = _('Could not find key unregister file.');
$errors[KEY_UNREG_FUNC_MISSING]       = _('Could not find key unregister function.');
$errors[KEY_RESTRICT_NO_TABLE]        = _('Key can not restrict items on phpws_key table alone.');
$errors[KEY_DUPLICATE]                = _('Duplicate key found.');

// Batch.php 1500-1599

// Cookie.php 1600-1699
$errors[COOKIE_SET_FAILED]            = _('Failed to write cookie.');

?>