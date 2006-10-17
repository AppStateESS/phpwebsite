<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function blog_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

    case version_compare($currentVersion, '0.1.1', '<'):
        $files[] = 'templates/version_view.tpl';
        $result = PHPWS_Boost::updateFiles($files, 'blog');
        if (!$result) {
            $content[] = 'Failed to add template file locally.';
            return FALSE;
        }
        $content[] = 'Fixed view version functionality.';

    case version_compare($currentVersion, '0.2.0', '<'):
        $db = new PHPWS_DB('blog_entries');
        $result = $db->addTableColumn('author_id', 'int NOT NULL default \'0\'', 'entry');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create column on blog_entries table.';
            return FALSE;
        }
        $result = $db->addTableColumn('approved', 'int NOT NULL default \'0\'');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create column on blog_entries table.';
            return FALSE;
        }

        $db->addValue('approved', 1);
        $result = $db->update();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to update blog_entries table.';
            return FALSE;
        }

        $db->reset();

        $db->setDistinct(1);
        $db->addJoin('left', 'blog_entries', 'users', 'author', 'username');
        $db->addColumn('author');
        $db->addColumn('users.id', NULL, 'author_id');
        $result = $db->select();
        if (!empty($result)) {
            $db->reset();
            foreach ($result as $user) {
                $db->addWhere('author', $user['author']);
                $db->addValue('author_id', $user['author_id']);
                $db->update();
                $db->reset();
            }
        }

    case version_compare($currentVersion, '0.2.5', '<'):
        if (!blog_update_025($content)) {
            return false;
        }

    case version_compare($currentVersion, '0.2.6', '<'):
        if (!blog_update_026($content)) {
            return false;
        }

    case version_compare($currentVersion, '1.0.0', '<'):
        if (!blog_update_100($content)) {
            return false;
        }

    case version_compare($currentVersion, '1.1.0', '<'):
        $db = new PHPWS_DB('blog_entries');
        $result = $db->addTableColumn('publish_date', 'int NOT NULL default \'0\'');
        $files = array();
        $files[] = 'templates/edit.tpl';
        PHPWS_Boost::updateFiles($files, 'blog');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to create publish_date column in blog_entries table.';
            return false;
        } else {
            $content[] = 'Blog entries now have a publish date setting.';
        }

    case version_compare($currentVersion, '1.1.1', '<'):
        $files = array();
        $files[] = 'templates/view.tpl';
        $files[] = 'templates/list_view.tpl';
        PHPWS_Boost::updateFiles($files, 'blog');
        $content[] = 'Updated to hAtom format.';
    }

    return true;
}


function blog_update_100(&$content)
{
    // main.tpl taken care of above
    $files[] = 'templates/edit.tpl';
    $result = PHPWS_Boost::updateFiles($files, 'blog');

    if (!PEAR::isError($result)) {
        $content[] = 'templates/edit.tpl file copied successfully.';
    } else {
        PHPWS_Error::log($result);
        $content[] = 'Blog update was unable to copy its updated template files locally.';
        return false;
    }

    $content[] = 'Fix - Changed dependency information for comments';
    $content[] = 'Fix - Uses new time functions';
    $content[] = 'Fix - main.tpl\'s title is under a panel-title class';
    $content[] = 'New - added summary section';
    $content[] = 'New - Edit links returned to blog view';
    $content[] = 'New - view.tpl Edit link added';
    return true;
}

function blog_update_025(&$content)
{
    $files[] = 'templates/list.tpl';
    $files[] = 'templates/edit.tpl';
    $files[] = 'templates/view.tpl';

    $result = PHPWS_Boost::updateFiles($files, 'blog');

    if (!PEAR::isError($result)) {
        $content[] = 'The following template files were successfully copied locally:';
        $content[] = implode('<br />', $files);
        $content[] = '<br />';
        return true;
    } else {
        PHPWS_Error::log($result);
        $content[] = 'Blog update was unable to copy its updated template files locally.';
        return false;
    }
}

function blog_update_026(&$content)
{
    $files[] = 'templates/view.tpl';
    $files[] = 'templates/main.tpl';
    $result = PHPWS_Boost::updateFiles($files, 'blog');

    if (!PEAR::isError($result)) {
        $content[] = 'The following template files were successfully copied locally:';
        $content[] = implode('<br />', $files);
        $content[] = '<br />';
        return true;
    } else {
        PHPWS_Error::log($result);
        $content[] = 'Blog update was unable to copy its updated template files locally.';
        return false;
    }

    $db = new PHPWS_DB('blog_entries');
    $result = $db->addTableColumn('summary', 'TEXT null');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = 'Unable to create column on blog_entries table.';
        return false;
    }
    $content[] = 'New - summary section to blogs.';
    $content[] = 'New - edit links on list view';
    $content[] = 'New - added panel-title class to main admin view';
    $content[] = 'Fix - approval view';

    return true;
}

?>