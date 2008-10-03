<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function miniadmin_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '0.0.5', '<'):
        $content[] = 'Fixed XHTML incompatibilities.';

    case version_compare($version, '1.0.0', '<'):
        $content[] = '- Changed to allow the addition of multiple yet single link submissions.';

    case version_compare($version, '1.0.1', '<'):
        $content[] = '<pre>
1.0.1 changes
------------------
+ Added translate function</pre>';

    case version_compare($version, '1.1.0', '<'):
        $content[] = '<pre>';
        $files = array('conf/config.php', 'templates/mini_admin.tpl', 'templates/alt_mini_admin.tpl');

        if (PHPWS_Boost::updateFiles($files, 'miniadmin')) {
            $content[] = '--- Successfully updated the following files:';
        } else {
            $content[] = '--- Was unable to copy the following files:';
        }
        $content[] = '     ' . implode("\n     ", $files);

        $content[] = '
1.1.0 changes
------------------
+ Added ability to pick different miniadmin template
+ Updated language functions.
</pre>';

    case version_compare($version, '1.1.1', '<'):
        $content[] = '<pre>
1.1.1 changes
------------------
+  Miniadmin was sending its content using "users" as the module.</pre>';

    case version_compare($version, '1.2.0', '<'):
        $content[] = '<pre>
1.2.0 changes
------------------
+ Added option to set the module title to a specific link.
+ PHP 5 formatted.</pre>';

    }
    return true;
}

?>
