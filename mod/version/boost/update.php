<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function version_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.3', '<'):
        $content[] = '+ Version now removes version tables if the module is using an uninstall.sql file.';

    case version_compare($currentVersion, '0.0.4', '<'):
        PHPWS_Boost::registerMyModule('version', 'users', $content);
        PHPWS_Boost::registerMyModule('version', 'controlpanel', $content);
        $content[] = '+ Added admin panel link (may need to log out and in)';

    case version_compare($currentVersion, '0.1.0', '<'):
        $content[] = '<pre>
+ Version will not spam tables it didn\'t remove when uninstalling a module.
+ Added some missing error logging.
+ Fixed default setting (Bug #1573480).
</pre>';

    case version_compare($currentVersion, '0.1.1', '<'):
        $content[] = '<pre>
+ Changed the way approval versions are pulled. Allows vr_creator to
  be 0 for anonymous submissions.
+ If the author is blank, version labels the author as Anonymous.
</pre>';

    case version_compare($currentVersion, '0.1.2', '<'):
        $content[] = '<pre>
0.1.2 changes
-------------
+ Added translate functions
</pre>';

    case version_compare($currentVersion, '0.1.3', '<'):
        $content[] = '<pre>
0.1.3 changes
-------------
+ Updated translation functions.
+ Added German translation files.
+ Changed control panel icon
</pre>';

    case version_compare($currentVersion, '0.1.4', '<'):
        $content[] = '<pre>
0.1.4 changes
-------------
+ Fixed Bug #1713230 : Changed getList to only pull specific columns if set.
</pre>';

    case version_compare($currentVersion, '0.1.5', '<'):
        $content[] = '<pre>
0.1.5 changes
-------------
+ Fixed logic for version deletion as recommended by blindman on bug
  #1678115
</pre>';

    case version_compare($currentVersion, '0.1.6', '<'):
        $content[] = '<pre>
0.1.6 changes
-------------
+ Many minor changes
+ New translations
</pre>';

    case version_compare($currentVersion, '0.1.6', '<'):
        $content[] = '<pre>
0.2.0 changes
-------------
+ PHP 5 formatting.
</pre>';
    }
    return TRUE;
}

?>
