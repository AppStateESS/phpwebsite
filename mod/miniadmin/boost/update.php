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

        case version_compare($version, '1.2.1', '<'):
            $content[] = '<pre>';
            miniadminUpdateFiles(array('templates/alt_mini_admin.tpl',
                'templates/mini_admin.tpl'), $content);
            $content[] = '1.2.1 changes
------------------
+ Wrapped div box-content around links per Obones patch submission
</pre>';

        case version_compare($version, '1.2.2', '<'):
            $content[] = '<pre>
1.2.2 changes
------------------
+ PHP 5 strict formatted.</pre>';

        case version_compare($version, '1.2.3', '<'):
            $content[] = '<pre>
1.2.3 changes
------------------
+ Miniadmin rewritten to work with Bootstrap theme.
+ Control panel link added as default to top of miniadmin.
</pre>';

        case version_compare($version, '1.2.4', '<'):
            $content[] = '<pre>
1.2.4 changes
------------------
+ Bootstrap compatibility changes
</pre>';
        case version_compare($version, '1.2.5', '<'):
            $content[] = <<<EOF
<pre>
1.2.5 changes
------------------
+ MiniAdmin with Control Panel link now shows up even if there aren't
  any active modules for the current page.
+ Administrative link only shows if user is logged in.
+ MiniAdmin list prevented from running off page. Using overflow control.
</pre>
EOF;
    }
    return true;
}

function miniadminUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'miniadmin')) {
        $content[] = '-- Successfully updated the following files:';
    } else {
        $content[] = '-- Unable to update the following files:';
    }
    $content[] = '    ' . implode("\n    ", $files);
    $content[] = '';
}

?>
