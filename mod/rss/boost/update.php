<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function rss_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '0.1.0', '<'):
        $content[] = '- Changed to binary safe file pull.';
        $content[] = '- Added system error checks and warnings.';

    case version_compare($version, '0.1.1', '<'):
        $content[] = '- Channel title now linked to feed.';
        $content[] = '- mod_rewrite works with feed.';

    case version_compare($version, '0.1.2', '<'):
        $content[] = '<pre>
0.1.2 changes
-------------
+ Added some error checks to feed translation.
</pre>';

    case version_compare($version, '0.1.3', '<'):
        $content[] = '<pre>
0.1.3 changes
-------------
+ Added translate functions
</pre>';

    case version_compare($version, '0.1.4', '<'):
        PHPWS_Boost::updateFiles(array('img/rss.png'), 'rss');
        $content[] = '<pre>
0.1.4 changes
-------------
+ Add German translation files.
+ Updated language functions.
+ Changed control panel icon
</pre>';


    }

    return true;
}

?>