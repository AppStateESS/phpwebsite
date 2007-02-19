<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function access_update(&$content, $version)
{
    switch (1) {
    case version_compare($version, '0.1.0', '<'):
        if (PHPWS_Boost::updateFiles(array('conf/config.php'), 'access')) {
            $content[] = '- Copied config.php locally.';
        } else {
            $content[] = '- Unable to copy config.php locally.';
        }
        $content[] = '- Added rewrite conditionals to .htaccess write.';

    case version_compare($version, '0.1.1', '<'):
        $content[] = '<pre>';
        $files = array('templates/main.tpl', 'templates/box.tpl', 'templates/shortcut_menu.tpl');
        if (PHPWS_Boost::updateFiles($files, 'access')) {
            $content[] = '-- Copied following template files locally:';
        } else {
            $content[] = '-- Failed to copy the following files locally:';
        }
        $content[] = implode("\n", $files);
        $content[] = '+ Fixed header tags (Bug #1652279)';
        $content[] = '</pre>';

    case version_compare($version, '0.1.2', '<'):
        $content[] = '<pre>
0.1.2 changes
---------------
+ Added translate functions.
</pre>
';
    }

    return true;
}

?>