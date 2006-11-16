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
    }

    return TRUE;
}


?>