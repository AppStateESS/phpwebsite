<?php

/**
 * @author Matthew McNaney
 * @version $Id$
 */

function related_update(&$content, $version)
{
    switch ($version) {
        case version_compare($version, '0.1.0', '<'):
            $content[] = 'Made links XHTML compatible.';

        case version_compare($version, '0.1.1', '<'):
            Key::registerModule('related');
            $content[] = 'Register module to key.';

        case version_compare($version, '0.1.2', '<'):
            $content[] = '<pre>
0.1.2 changes
--------------
+ Added translate functions
</pre>';

        case version_compare($version, '0.1.3', '<'):
            $content[] = '<pre>
0.1.3 changes
--------------
+ Added German translation.
+ Updated translation functions.
</pre>';

        case version_compare($version, '0.1.4', '<'):
            $content[] = '<pre>
0.1.4 changes
--------------
+ Added uninstall.php file.
+ Removed uninstall.sql.
</pre>';

        case version_compare($version, '0.1.5', '<'):
            $content[] = '<pre>
0.1.5 changes
--------------
+ Updated English translation.
</pre>';

        case version_compare($version, '0.2.0', '<'):
            $content[] = '<pre>
0.2.0 changes
--------------
+ PHP 5 formatted.
</pre>';

        case version_compare($version, '0.2.1', '<'):
            $files = array('templates/bank.tpl',
                       'templates/create.tpl',
                       'templates/edit.tpl');
            PHPWS_Boost::updateFiles($files, 'related');
            $content[] = '<pre>
0.2.1 changes
--------------
+ Patch #2501401 from Olivier Sannier - Some h1 tags added.
</pre>';

        case version_compare($version, '0.2.2', '<'):
            $content[] = '<pre>
0.2.2 changes
--------------
+ PHP 5 strict formatted.
+ Icon class implemented.
</pre>';

    }

    return true;
}

?>