<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

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

		       define('FULL_PERM_NAME', dgettext('users', 'Unrestricted'));
		       define('PART_PERM_NAME', dgettext('users', 'Restricted'));
		       define('NO_PERM_NAME', dgettext('users', 'None'));

		       /**
		        * reg exp characters to strip from username default is '\w'
		        * or only alphanumeric characters or an underline
		        * read up about regular expressions before editing
		        */
		       define('ALLOWED_USERNAME_CHARACTERS', '\w');


		       /**
		        * number of hours new users are able to confirm their account
		        */
		       define('NEW_SIGNUP_WINDOW', 48);

		       // Enter the minimum character
		       // count allowed for each
		       define('PASSWORD_LENGTH', 5);
		       define('USERNAME_LENGTH', 3);
		       define('DISPLAY_NAME_LENGTH', 4);
		       define('GROUPNAME_LENGTH', 4);

		       define('LOGIN_BUTTON', dgettext('users', 'Login'));
		       define('USER_SIGNUP_QUESTION', dgettext('users', 'Want to join?'));

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

		       /**
		        * This should remain untouched. You do not want deities to automatically
		        * be able to log in to the system using Remember Me. If you
		        * REALLY want to allow this, change it to true.
		        */
		       define('ALLOW_DEITY_REMEMBER_ME', false);

		       /**
		        * number of days a remember me cookie should last
		        */
		       define('REMEMBER_ME_LIFE', 365);

		       /**
		        * The default value for this is false. Deities should not
		        * have their passwords reset. They should be able to fix them manually.
		        * That said, if you set this to true, phpWebSite will allow the
		        * reset. It should be changed back to false after logging in.
		        */
		       define('ALLOW_DEITY_FORGET', false);

		       ?>