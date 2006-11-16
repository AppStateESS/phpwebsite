If freecap is not working for you, make sure your config.php does NOT
contain the following:

/**
 * To keep this file a little tidier, file and image types are listed
 * in the file_types.php file. Make sure you know how to edit
 * arrays before altering the file.
 */ 
include 'config/core/file_types.php';

Just delete it all.

Make sure that the following IS in your config.php file:
define('ALLOW_CAPTCHA', true);
define('CAPTCHA_NAME', 'freecap');
