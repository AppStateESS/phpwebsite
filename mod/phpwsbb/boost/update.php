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

                   case version_compare($currentVersion, '2.0.5', '<'):
            $content[] = '<pre>
2.0.5 changes
--------------
+ Fixed NOT NULL constraints in install sql.
+ (24 August 2009) Fixed problems with the forum listing
+ (9 June 2009) Patch #2795892 - Fixed translation typo. Thanks HanV</pre>';

        case version_compare($currentVersion, '2.0.6', '<'):

            $db = new PHPWS_DB();
            $db->query('ALTER TABLE phpwsbb_topics DROP views');
            $content[] = '+ Deleted obsolete column "views" from phpwsbb_topics table';
            
            $content[] = '<pre>
========================
Changes in version 2.0.6
========================
+ Got rid of obsolete phpwsbb_topics.views column
+ Topic now uses Key->times_viewed to store pageviews
+ UI improvement: "Create New Topic" button is now always visible regardless of authorization
+ phpwsbb now uses the core errorPages function for display of error messages
+ Added error messages when a new topic is missing a title or body text
+ Topic editing screen now distnguishes between "Editing" or "Creating" in the title 
+ Icons are now themable via the core Icon class
+ Fixed inefficient query generated when listing topics in a forum.
+ PHP5 fixes
</pre>';
            

    } // end of switch

    PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
    PHPWSBB_Data::clearCaches();
    $content[] = 'Cleared all phpwsbb caches';

    return true;
}

?>