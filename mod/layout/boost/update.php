<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function layout_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '2.2.0', '<'):
        $content[] = 'This package will not update versions under 2.2.0.';
        return false;

    case version_compare($currentVersion, '2.2.1', '<'):
        $content[] = '+ Fixed improper sql call in update_220.sql file.';

    case version_compare($currentVersion, '2.3.0', '<'):
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles(array('conf/config.php', 'conf/error.php'), 'layout')) {
            $content[] = 'Updated conf/config.php and conf/error.php file.';
        } else {
            $content[] = 'Unable to update conf/config.php and conf/error.php file.';
        }
        $content[] = '
2.3.0 changes
-------------
+ Removed references from object constructors.
+ Added the plug function to allow users to inject content directly
  into a theme.
+ Added translate functions.
+ Layout now looks for and includes a theme\'s theme.php file.
</pre>';

    }
    return true;
}

?>