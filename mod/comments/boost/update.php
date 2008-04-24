<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function comments_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '0.6.2', '<'):
        $content[] = '<pre>Comments versions prior to 0.6.2 are not supported for updating.
Please download 0.6.3.</pre>';
        break;

    case version_compare($currentVersion, '0.6.3', '<'):
        $content[] = '<pre>';
        $files = array('templates/alt_view.tpl', 'templates/view.tpl');
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = '---The following templates copied locally.';
        } else {
            $content[] = '---The following templates failed to copy locally.';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '
0.6.3 Changes
-------------
+ Added setAnchor to comments.
+ Changed anchor tag to conform with Safari.
</pre>';


    case version_compare($currentVersion, '0.6.4', '<'):
        $content[] = '<pre>';
        $files = array('templates/settings_form.tpl', 'templates/recent.tpl');
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = '---The following templates copied locally.';
        } else {
            $content[] = '---The following templates failed to copy locally.';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '
0.6.4 Changes
-------------
+ RFE #1720589 - Added ability to show most recent comments in a popup.
+ Added permission check on single comment view.
</pre>';

    case version_compare($currentVersion, '1.0.0', '<'):
        $content[] = '<pre>';
        $db = new PHPWS_DB('comments_users');
        if (PHPWS_Error::logIfError($db->createTableIndex('user_id', null, true))) {
            $content[] = 'Warning: A problems occurred when trying to create a unique index on the comments_users table.';
        }

        $files = array('javascript/report/head.js', 'javascript/report/default.php', 'javascript/admin/head.js', 
                       'templates/alt_view.tpl', 'templates/alt_view_one.tpl', 'templates/view.tpl', 
                       'templates/view_one.tpl', 'templates/punish_pop.tpl', 'templates/reported.tpl',
                       'templates/style.css', 'img/lock.png');

        commentsUpdatefiles($files, $content);
        PHPWS_Boost::registerMyModule('comments', 'controlpanel', $content);
        PHPWS_Boost::registerMyModule('comments', 'users', $content);

        if (!PHPWS_Boost::inBranch()) {
            $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/comments/boost/changes/1_0_0.txt');
        }
        $content[] = '</pre>';

    case version_compare($currentVersion, '1.0.1', '<'):
        $content[] = '<pre>';
        $db = new PHPWS_DB('comments_items');
        $result = $db->addTableColumn('reported', 'smallint NOT NULL default 0');
        if (PHPWS_Error::logIfError($result)) {
            $content[] = 'Unable to create reported column on comments_items table.</pre>';
            return false;
        } else {
            $content[] = 'Table column added.';
        }
        $content[] = '1.0.1 Changes
-------------
+ Fixed missing reported column on comments_items table.</pre>';

    case version_compare($currentVersion, '1.1.0', '<'):
        $content[] = '<pre>';
        $db = new PHPWS_DB('comments_threads');
        $result = $db->addTableColumn('approval', 'smallint NOT NULL default 0');
        if (PHPWS_Error::logIfError($result)) {
            $content[] = 'Unable to create approval column on comments_threads table.</pre>';
            return false;
        } else {
            $content[] = 'Table column added to comments_threads.';
        }

        $db = new PHPWS_DB('comments_items');
        $result = $db->addTableColumn('approved', 'smallint NOT NULL default 1');
        if (PHPWS_Error::logIfError($result)) {
            $content[] = 'Unable to create approved column on comments_items table.</pre>';
            return false;
        } else {
            $content[] = 'Table column added to comments_items.';
        }

        $files = array('img/cancel.png', 'img/noentry.png', 'img/ok.png', 'templates/approval.tpl',
                       'templates/settings_form.tpl', 'javascript/quick_view/head.js');
        commentsUpdateFiles($files, $content);

        $content[] = '1.1.0 Changes
-------------
+ Comments can be approved before posting.
</pre>';


    }
            
    return true;
}


function commentsUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'comments')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "     " . implode("\n     ", $files);
}


?>