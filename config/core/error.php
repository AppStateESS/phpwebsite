<?php
translate("core");
// Generic
$errors[PHPWS_UNKNOWN]               = _('Unknown Error');
$errors[PHPWS_FILE_NOT_FOUND]        = _('File not found');
$errors[PHPWS_CLASS_NOT_CONSTRUCTED] = _('Tried to access a class that is not constructed.');

// Database.php 100 - 199
$errors[PHPWS_DB_ERROR_TABLE]        = _('Table name not set');
$errors[PHPWS_DB_NO_CLASS]           = _('Missing or non-existant class name');
$errors[PHPWS_DB_NO_VALUES]          = _('No values were set before the query');

// Manager.php 200 - 299
$errors[MANAGER_ERR_MODULE_NOT_SET]  = _('Manager module not set');
$errors[MANAGER_ERR_TABLE_NOT_SET]   = _('Manager table name not set');
$errors[MANAGER_ERR_CLASS_NOT_SET]   = _('Include set without class');
$errors[MANAGER_INC_FILE_NOT_FOUND]  = _('Include set but not found');
$errors[MANAGER_CLASS_NON_EXISTS]    = _('Class could not be created');


?>