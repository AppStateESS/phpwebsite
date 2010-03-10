<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function profiler_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '0.3.0', '<'):
            $content[] = 'This package does not update versions under 0.3.0';
            return false;

        case version_compare($currentVersion, '0.3.1', '<'):
            $content[] = '<pre>
0.3.1 changes
----------------
+ Added translate functions.
</pre>
';

        case version_compare($currentVersion, '0.3.2', '<'):
            PHPWS_Boost::updateFiles(array('img/profile.png'), 'profiler');
            $content[] = '<pre>
0.3.2 changes
----------------
+ Updated language version
+ Added German files
+ Changed control panel icon
</pre>
';

        case version_compare($currentVersion, '0.3.3', '<'):
            PHPWS_Boost::updateFiles(array('templates/forms/edit.tpl'), 'profiler');
            $content[] = '<pre>
0.3.3 changes
---------------
+ Updated image manager code.</pre>';

        case version_compare($currentVersion, '0.4.0', '<'):
            $content[] = '<pre>';
            PHPWS_Boost::updateFiles(array('templates/forms/division_list.tpl'), 'profiler');
            $content[] = '0.4.0 changes
---------------
+ Website address is not required.
+ Supports new File Cabinet changes.
+ Can now delete divisions.
+ Change made to allow accented characters for division names.
</pre>';

        case version_compare($currentVersion, '0.5.0', '<'):
            $content[] = '<pre>0.5.0 changes
---------------
+ PHP 5 format.</pre>';


    }

    return true;
}

?>