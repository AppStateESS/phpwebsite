<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function webpage_update(&$content, $currentVersion)
{

    switch ($currentVersion) {
    case version_compare($currentVersion, '0.2.0', '<'):
        $content[] = '+ Add ability to join all pages together';
        $content[] = '+ Fixed xhtml issues with links';
        $content[] = '+ Front page should no longer pull unapproved pages.';

    case version_compare($currentVersion, '0.2.1', '<'):
        $content[] = '+ Added parseTags to content.';

    case version_compare($currentVersion, '0.2.3', '<'):
        $files = array();
        $files[] = 'templates/forms/list.tpl';
        $result = PHPWS_Boost::updateFiles($files, 'users');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Unable to update templates/forms/list.tpl';
        } else {
            $content[] = 'Template file updated.';
        }

        $db = & new PHPWS_DB('webpage_volume');
        $result = $db->addTableColumn('active', 'smallint NOT NULL default 0');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Failed adding active column to webpage_volume table.';
        } else {
            $content[] = 'New - Added active column to admin view and table.';
        }

    case version_compare($currentVersion, '0.2.4', '<'):
        $files = array();
        $files[] = 'templates/page/basic.tpl';
        $files[] = 'templates/page/prev_next.tpl';
        $files[] = 'templates/page/short_links.tpl';
        $files[] = 'templates/page/verbose_links.tpl';
        if (PHPWS_Boost::updateFiles($files, 'webpage')) {
            $content[] = 'Template files updated.';
        } else {
            $content[] = 'Template file not updated successfully.';
        }
        $content[] = 'Added commenting to page templates to prevent empty titles.';

    case version_compare($currentVersion, '0.2.5', '<'):
        $content[] = '<pre>
0.2.5 changes
-------------
+ Fixed bug that was using the wrong tab when editing the header.
+ Removed old update code and a repeat in this file.
</pre>';

    case version_compare($currentVersion, '0.2.6', '<'):
        $content[] = '<pre>
0.2.6 changes
-------------
+ Moved search save to Volume class
+ Searches now reset search key words to prevent lost searches.
</pre>';

    case version_compare($currentVersion, '0.3.0', '<'):
        $files = array('templates/page/basic.tpl',
                       'templates/page/prev_next.tpl',
                       'templates/page/short_links.tpl',
                       'templates/page/verbose_links.tpl');
        if (PHPWS_Boost::updateFiles($files, 'webpage')) {
            $content[] = 'Template files updated successfully.';
        } else {
            $content[] = 'Template files not updated.';
        }
        $db = new PHPWS_DB('webpage_page');
        $result = $db->addTableColumn('image_id', 'int NOT NULL default 0');
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $content[] = 'Failed adding image_id column to webpage_page table.';
            return false;
        }

        $content[] = '<pre>
0.3.0 changes
-------------
+ Added simple image page inclusion.
</pre>';

    case version_compare($currentVersion, '0.4.0', '<'):
        if (!PHPWS_DB::isTable('webpage_featured')) {
            $db2 = new PHPWS_DB('webpage_featured');
            $db2->addValue('id', 'int NOT NULL default 0');
            $db2->addValue('vol_order', 'int NOT NULL default 0');
            $result = $db2->createTable();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'Unable to create webpage_featured table.';
                return false;
            }
        }
        $content[] = '<pre>
0.4.0 changes
-------------
+ Added "Featured" option. Lets you promote specific web pages summaries.
+ Added ability to move pages inside volumes.
</pre>';

    }

    return TRUE;
}


?>