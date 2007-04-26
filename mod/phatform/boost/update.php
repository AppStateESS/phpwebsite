<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function phatform_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '3.0.2', '<'):
        $content[] = '- Fixed compatibility issues.';

    case version_compare($version, '3.0.3', '<'):
        $content[] = '- Fixed element move bug.';

    case version_compare($version, '3.0.4', '<'):
        $content[] = '<pre>
3.0.4 changes
-------------
+ Simplified install.sql
+ Fixed some incompatible errorMessage function calls
</pre>';

    case version_compare($version, '3.0.5', '<'):
        $content[] = '<pre>
3.0.5 changes
-------------
+ Fixed typo in Form_Manager class causing crashes.
</pre>';

    case version_compare($version, '3.0.6', '<'):
        $content[] = '<pre>
3.0.6 changes
-------------
+ Added translate call.
+ Added missing "export" directory creation.
+ Removed all global core calls
+ Fixed email bug.
</pre>';

    case version_compare($version, '3.1.0', '<'):
        PHPWS_Boost::updateFiles(array('img/phatform.png'), 'phatform');
        $content[] = '<pre>
3.1.0 changes
-------------
+ Added German translations
+ Update language functions.
+ Changed control panel icon
</pre>';
    }
    return true;
}

?>