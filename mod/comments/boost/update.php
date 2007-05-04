<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function comments_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.4.0', '<'):
        $content[] = 'This package will not update versions under 0.4.0.';
        return false;

    case version_compare($currentVersion, '0.5.0', '<'):
        $files = array();
        $files[] = 'templates/view.tpl';
        $files[] = 'templates/alt_view.tpl';
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = 'Templates copied locally.';
        } else {
            $content[] = 'Templates failed to copy locally.';
        }

        $content[] = '<pre>
0.5.0 Changes
-------------
+ Updated templates templates/view.tpl, templates/alt_view.tpl
+ Added anchor tag to templates and code.
+ Changed the getSourceUrl function in the Thread to use the DBPager\'s
  new saveLastView and getLastView functions.
+ Update dependent on new core.
</pre>';

    case version_compare($currentVersion, '0.5.1', '<'):
        $db = new PHPWS_DB('comments_items');
        $result = $db->addTableColumn('anon_name', 'varchar(30) default NULL');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Error encountered while trying to update comments_items table.';
            return false;
        } else {
            $content[] = 'comments_items table updated successfully.';
        }

        $content[] = '<pre>';
        $files = array( 'conf/config.php', 'conf/forbidden.php',
                        'templates/edit.tpl', 'templates/settings_form.tpl',
                        'templates/alt_view.tpl', 'templates/alt_view_one.tpl',
                        'templates/view.tpl', 'templates/view_one.tpl');
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = '+ The following templates copied locally.';
        } else {
            $content[] = '+ The following templates failed to copy locally.';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '
0.5.1 Changes
-------------
+ Option for anonymous users to enter a name to the comment.
+ Added translate functions.
+ Fixed isPosted check to prevent extra posts
</pre>';

    case version_compare($currentVersion, '0.6.0', '<'):
        PHPWS_Boost::updateFiles(array('img/comments.png'), 'comments');
        $content[] = '<pre>
0.6.0 Changes
-------------
+ Changed control panel icon.
+ Updated language functions.
</pre>';

    case version_compare($currentVersion, '0.6.1', '<'):
        $files = array('templates/alt_view.tpl', 'templates/view.tpl');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'comments')) {
            $content[] = '---The following templates copied locally.';
        } else {
            $content[] = '---The following templates failed to copy locally.';
        }
        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '0.6.1 Changes
-------------
+ Added a "comments" anchor.
+ Changed translatation method for numbering comments.
</pre>';

    case version_compare($currentVersion, '0.6.2', '<'):
        $content[] = '<pre>0.6.2 Changes
-------------
+ Fixed bug with age ordering.</pre>';
    }
            
    return true;
}

?>