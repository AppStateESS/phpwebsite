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


Final note: FreeCap has 4 background types. If you use bg_type = 3 (the
default) you may encounter memory problems with PHP 4. Increase your
limit to 16mb or more. PHP 5 doesn't seem to have this problem.
