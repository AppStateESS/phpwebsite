<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


function users_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '2.2.0', '<'):
        $content[] = 'This package does not update versions under 2.2.0';
        return false;

    case version_compare($currentVersion, '2.2.1', '<'):
        $content[] = '+ Fixed a bug causing conflicts between user and group permissions.';

    case version_compare($currentVersion, '2.2.2', '<'):
        $content[] = '+ Set username to the same character size in both users table and user_authorization.';
        $content[] = '+ Fixed typo causing branch installation failure on Postgresql.';

    case version_compare($currentVersion, '2.3.0', '<'):
        $content[] = '<pre>
2.3.0 changes
------------------------
+ Added translate function calls in classes and my_page.php
+ my_page hides translation option if language defines disable selection
+ Added a unrestricted only parameter to Current_User\'s allow and
  authorize functions
+ Dropped references from some constructors
+ Added error check to setPermissions function: won\'t accept empty
  group id
+ Changed id default to zero.
+ Removed unneeded function parameter on getGroups
</pre>
';

    case version_compare($currentVersion, '2.3.1', '<'):
        $content[] = '<pre>';
        $files = array('templates/my_page/user_setting.tpl');
        if (PHPWS_Boost::updateFiles($files, 'users')) {
            $content[] = 'Successfully updated my_page/user_setting.tpl file.';
        } else {
            $content[] = 'Unable to update my_page/user_setting.tpl file.';
        }
        $content[] = '
2.3.1 changes
------------------------
+ Added ability for user to set editor preferences
</pre>
';

    case version_compare($currentVersion, '2.3.2', '<'):
        $content[] = '<pre>2.3.2 changes';
        $files = array('img/users.png', 'templates/user_main.tpl');
        if (PHPWS_Boost::updateFiles(array('img/users.png'), 'users')) {
            $content[] = '+ Updated the following files:';
        } else {
            $content[] = '+ Unable to update the following files:';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '+ Added error check to login.
+ Changed user control panel icon.
+ Fixed template typo that broke IE login.
+ Removed fake French translation (delete mod/users/locale/fr_FR/ directory
+ Permissions are now ordered alphabetically.
+ isUser will now always return false if passed a zero id.
+ Added new function requireLogin that forwards a user to the login
  screen
</pre>';

    case version_compare($currentVersion, '2.4.0', '<'):
        if (!PHPWS_DB::isTable('users_pw_reset')) {
            $new_table = 'CREATE TABLE users_pw_reset (
user_id INT NOT NULL default 0,
authhash CHAR( 32 ) NOT NULL default 0,
timeout INT NOT NULL default 0,
);';
            if (!PHPWS_DB::import($new_table)) {
                $content[] = 'Unable to create users_pw_reset table.';
                return false;
            } else {
                $content[] = 'Created new table: users_pw_reset';
            }
        }
        $files = array('templates/forms/reset_password.tpl', 'templates/forms/forgot.tpl',
                       'conf/config.php', 'templates/usermenus/top.tpl', 'templates/forms/settings.tpl',
                       'templates/my_page/user_setting.tpl');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'layout')) {
            $content[] = '--- Successfully updated the following files:';
        } else {
            $content[] = '--- Was unable to copy the following files:';
        }
        $content[] = '     ' . implode("\n     ", $files);

        $content[] = '
2.4.0 changes
------------------------
+ Permissions just close the popup window instead of displaying
  message.
+ Moved include to prevent pre-defined error.
+ Added Forgot password and Forgot username functionality
+ Started password reset and user name reminder options.
+ Added comments to user menu template to prevent extra characters
+ Implemented RFE 1628318 - Remember me option on users module.
+ Rewrote logout functionality to work with remember me
+ Hitting user\'s index.php forwards to the 404 page
+ Removed redundant class calls in init.php
+ Changed default username size to 3
+ Updated translation functions.
+ Changed form names on login templates since both have ids and may
  appear on same page.
+ Removed return by reference from Current_User::getUserObj
+ Changed popuppermission to echo error instead of using goBack
  function.
+ Fixed error code call in Permissions.php. Needed to call core code.
+ Added German translation files
</pre>';

    case version_compare($currentVersion, '2.4.1', '<'):
        $content[] = '<pre>';
        $files = array('conf/languages.php');
        if (PHPWS_Boost::updateFiles($files, 'users')) {
            $content[] = 'Successfully updated conf/languages.php file.';
        } else {
            $content[] = 'Unable to update conf/languages.php file.';
        }
        $content[] = '
2.4.1 changes
------------------------
+ Default item id on permission check functions is now zero instead of
  null. This will make checking permissions a little easier on new items.
+ Bug #1690657 - Changed group select js property to onclick instead
  of onchange. Thanks singletrack.
+ Changed the language abbreviation for Danish
</pre>
';

    } // End of switch statement

    return TRUE;

}

?>