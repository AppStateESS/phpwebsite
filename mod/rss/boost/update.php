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

    case version_compare($version, '0.2.0', '<'):
        $db = new PHPWS_DB('rss_channel');
        PHPWS_Error::logIfError($db->dropTableColumn('last_build_date'));
        $content[] = '<pre>';
        $files = array('templates/rss20.tpl', 'templates/settings.tpl');
        if (PHPWS_Boost::updateFiles($files, 'rss')) {
            $content[] = '--- Successfully updated the following files:';
        } else {
            $content[] = '--- Could NOT update the following files successfully:';
        }

        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '
0.2.0 changes
---------------
+ RSS 2.0 is now useable.
+ Added settings page for some of the RSS 2.0 options
+ Removed a header line that caused some verification errors in the 2.0 template.
+ Changed template process. Some fields were missing from the feeds.
';

    case version_compare($version, '0.2.1', '<'):
        $content[] = '<pre>
0.2.1 changes
-------------
+ Changed rss popup window dimensions and form text length
</pre>';

    case version_compare($version, '0.2.2', '<'):
        $content[] = '<pre>
0.2.2 changes
-------------
+ Added line of code to Feed to prevent error on bad data.
+ Added Vietnamese translation.
</pre>';

    case version_compare($version, '0.2.3', '<'):
        PHPWS_Boost::updateFiles(array('templates/admin_feeds.tpl'), 'rss');
        $content[] = '<pre>
0.2.3 changes
-------------
+ Added error check on feed listing.
+ Shortened urls
+ Added sort button to feed listing.
</pre>';

    case version_compare($version, '0.2.4', '<'):
        $content[] = '<pre>
0.2.4 changes
-------------
+ Fixed the timeout setting for the rss caching.
</pre>';

    case version_compare($version, '0.2.5', '<'):
        $content[] = '<pre>
0.2.5 changes
-------------
+ Mod rewrite link added to rss feed.
+ High ascii characters have their ampersand parsed.
</pre>';


    }

    return true;
}

?>