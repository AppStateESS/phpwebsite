<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */


function block_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '1.1.1', '<'):
            $content[] = '<pre>Block versions prior to 1.1.1 are not supported for updating.
Please download version 1.1.2.</pre>';
            break;

        case version_compare($currentVersion, '1.1.2', '<'):
            PHPWS_Boost::updateFiles(array('img/block.png'), 'block');
            $content[] = '<pre>1.1.2 changes
-------------
+ Added German files
+ Use new translation format
+ Changed control panel icon
</pre>';

        case version_compare($currentVersion, '1.1.3', '<'):
            $content[] = '<pre>';
            if (PHPWS_Boost::updateFiles(array('templates/sample.tpl'), 'block')) {
                $content[] = '--- Successfully copied templates/sample.tpl';
            } else {
                $content[] = '--- Unable to copy templates/sample.tpl';
            }
            $content[] = '
1.1.3 changes
-------------
+ Changed the sample.tpl layout to conform with other "box" templates.
</pre>';

        case version_compare($currentVersion, '1.2.0', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('block');
            if (PHPWS_Error::logIfError($db->addTableColumn('file_id', 'int not null default 0'))) {
                $content[] = 'Unable to add file_id column to block table.</pre>';
                return false;
            }

            if (PHPWS_Error::logIfError($db->addTableColumn('hide_title', 'smallint not null default 0'))) {
                $content[] = 'Unable to add file_only column to block table.</pre>';
                return false;
            }
            if (PHPWS_Boost::updateFiles(array('conf/config.php', 'templates/edit.tpl', 'templates/sample.tpl'),
                                     'block')) {
            $content[] = '--- Successfully updated files.';
                                     } else {
                                         $content[] = '--- Unable to update files. Please run revert in Boost.';
                                     }

                                     $content[] = '1.2.0 changes
-----------------
+ Blocks can now contain File Cabinet elements.</pre>';

        case version_compare($currentVersion, '1.3.0', '<'):
            if (PHPWS_Boost::updateFiles(array('templates/list.tpl', 'templates/sample.tpl'), 'block')) {
                $content[] = '--- Successfully copied templates/list.tpl';
            } else {
                $content[] = '--- Unable to copy templates/list.tpl';
            }

            $content[] = '<pre>1.3.0 changes
-----------------
+ PHP 5 updated
+ Wrapped block-file class around file display.
+ Changed pager view to use addSortHeaders</pre>';

        case version_compare($currentVersion, '1.3.1', '<'):
            if (PHPWS_Boost::updateFiles(array('templates/settings.tpl'), 'block')) {
                $content[] = '--- Successfully copied templates/settings.tpl';
            } else {
                $content[] = '--- Unable to copy templates/settings.tpl';
            }

            $content[] = '<pre>1.3.1 changes
-----------------
+ RFE #2236544 - Image size limits set in settings tab instead of in
  config file.
</pre>';

        case version_compare($currentVersion, '1.3.2', '<'):
            $content[] = '<pre>1.3.2 changes
-------------
+ Removed isPosted check on block save.
</pre>';

        case version_compare($currentVersion, '1.3.3', '<'):
            $content[] = '<pre>1.3.3 changes
-------------
+ PHP 5 strict changes.
+ Using Icon class.
+ Id added to each block.
</pre>';
        case version_compare($currentVersion, '1.3.4', '<'):
            $content[] = '<pre>1.3.4 changes
--------------
+ Removed exit call.
</pre>';
    }
    return TRUE;
}

?>