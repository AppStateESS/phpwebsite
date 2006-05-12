<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */ 

translate('users');
define('BAD_PASSWORDS',
       serialize(array('pass',
		       'password',
		       'pssword',
		       'asdfg',
		       'qwerty',
		       'phpwebsite',
		       'admin',
		       'phpws',
		       'asdlkj'	       
		       )
		 )
       );


define('DEFAULT_ITEMNAME', 'common');
define('DEFAULT_USER_MENU', 'new_user');

define('UNRESTRICTED_PERMISSION',    2);
define('RESTRICTED_PERMISSION', 1);
define('NO_PERMISSION',      0);

define('FULL_PERM_NAME', _('Unrestricted'));
define('PART_PERM_NAME', _('Restricted'));
define('NO_PERM_NAME', _('None'));

/**
 * reg exp characters to strip from username default is '\w'
 * or only alphanumeric characters or an underline
 * read up about regular expressions before editting
 */
define('ALLOWED_USERNAME_CHARACTERS', '\w');


/**
 * number of hours new users are able to confirm their account
 */
define('NEW_SIGNUP_WINDOW', 48);

// Enter the minimum character
// count allowed for each
define('PASSWORD_LENGTH', 5);
define('USERNAME_LENGTH', 4);
define('DISPLAY_NAME_LENGTH', 4);
define('GROUPNAME_LENGTH', 4);

define('LOGIN_BUTTON', _('Login'));
define('USER_SIGNUP_QUESTION', _('Want to join?'));

// phpWebSite uses Pear's graphic confirmation class
// You must set the correct font path and file for it to 
// function 
define('ENABLE_GRAPHIC_CONFIRMATION', TRUE);
define('GC_FONT_SIZE', 22);
define('GC_FONT_PATH', '/usr/share/fonts/bitstream-vera/');
define('GC_FONT_FILE', 'Vera.ttf');
define('GC_WIDTH', 200);
define('GC_HEIGHT', 70);

/** Authorization Mode
 * Leave this alone
 */
define('LOCAL_AUTHORIZATION', 1);
define('GLOBAL_AUTHORIZATION', 2);

translate();
?>