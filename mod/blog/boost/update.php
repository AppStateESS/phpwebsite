<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function blog_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

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

    case version_compare($currentVersion, '1.2.0', '<'):
        $files = array();
        $files[] = 'conf/config.php';
        $files[] = 'templates/recent_view.tpl';
        $files[] = 'templates/settings.tpl';
        $files[] = 'templates/past_view.tpl';
        
        $content[] = 'Change - moved cache key out of config';
        $content[] = 'New - Settings tab allows control of view options for blog.';
        $content[] = 'New - Past Entries box shows older blog entries.';
        $content[] = 'New - Admin has ability to show blog on separate page.';

    case version_compare($currentVersion, '1.2.1', '<'):
        $files = array();
        $files[] = 'templates/recent_view.tpl';
        $files[] = 'templates/settings.tpl';
        $files[] = 'templates/past_view.tpl';
        PHPWS_Boost::updateFiles($files, 'blog');
        $content[] = 'Changed classes to ids in recent and past view templates.';
        $content[] = 'Fixed typo in settings template.';
        $content[] = 'Added error check when no blog entries are present.';
        $content[] = 'Changed default settings.';

    case version_compare($currentVersion, '1.2.2', '<'):
        $content[] = '<pre>
1.2.2 Changes
-------------
+ Entry viewing now adds the blog title to the page title.
</pre>';

    case version_compare($currentVersion, '1.2.3', '<'):
        $content[] = '<pre>
1.2.3 Changes
-------------
+ Make call to resetKeywords in search to prevent old search word retention.
</pre>';

    case version_compare($currentVersion, '1.3.0', '<'):
        $files = array('templates/edit.tpl', 'templates/settings.tpl', 'templates/submit.tpl', 'templates/user_main.tpl');
        if (PHPWS_Boost::updateFiles($files, 'blog')) {
            $content[] = 'Template files copied successfully.';
        } else {
            $content[] = 'Failed to copy template files successfully.';
        }

        $content[] = '<pre>
1.3.0 Changes
-------------
+ Added ability for anonymous and users without blog permission to
  submit entries for later approval.
+ Added setting to allow anonymous submission.
</pre>';
    }

    return true;
}

?>