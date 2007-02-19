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

    }

    return TRUE;
}

?>