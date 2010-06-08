<?php

/**
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @version $Id: update.php,v 1.29 2009/01/22 01:36:12 adarkling Exp $
 */

function phpwsbb_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

        case version_compare($currentVersion, '2.0.1', '<'):
            $content[] = '<pre>
========================
Changes in version 2.0.1
========================
+ Fixed a bug that was preventing the first Forum from being created.  Thanks, jmullan99!
+ Message Boards & Latest Forum Posts blocks now only appear on the home page.
+ Message Boards & Latest Forum Posts blocks now have their own template files.
</pre>';

        case version_compare($currentVersion, '2.0.2', '<'):
            $files = array('templates/forum_list.tpl', 'templates/forum.tpl');
            if (PHPWS_Boost::updateFiles($files, 'phpwsbb')) {
                $content[] = '+ Updated the following files:     ' . implode("\n     ", $files);
            }
            else {
                $content[] = '+ Unable to update the following files:     ' . implode("\n     ", $files);
                return false;
            }

            $content[] = '<pre>
========================
Changes in version 2.0.2
========================
+ Fixed "Fatal error: Class "PHPWSBB_Forms" not found" bug
+ Adjusted formatting of forum.tpl
+ Minor php5 compatibility change
</pre>';

        case version_compare($currentVersion, '2.0.3', '<'):
            $content[] = '<pre>
========================
Changes in version 2.0.3
========================
+ Forms use "break post" now.
+ Memory usage optimization
+ Fixed approval bug reported by Verdon http://www.phpwsforums.com/showpost.php?p=30440
+ Improved display of unapproved topics
+ Forum List "Topics" counter was including unapproved articles.  Fixed.
+ Fixed homepage filter bug reported by Verdon http://www.phpwsforums.com/showpost.php?p=30460
+ Fixed topic permissions bug reported by Verdon http://www.phpwsforums.com/showpost.php?p=30460
</pre>';

        case version_compare($currentVersion, '2.0.4', '<'):
            $content[] = '<pre>
========================
Changes in version 2.0.4
========================
+ Changed topics\' edit permission to "manage forums" SuperModerators\' comments will always be approved no matter what.
+ "Create Topic" form now only asks for an anonymous name if both Comments & the Forum settings allow it.
</pre>';

                   case version_compare($currentVersion, '2.0.4', '<'):
            $content[] = '<pre>
2.0.5 changes
--------------
+ Fixed NOT NULL constraints in install sql.
+ Fixed problems with forum listing displays.</pre>';
            

    } // end of switch

    \core\Core::initModClass('phpwsbb', 'BB_Data.php');
    PHPWSBB_Data::clearCaches();
    $content[] = 'Cleared all phpwsbb caches';

    return true;
}

?>