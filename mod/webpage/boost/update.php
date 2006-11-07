<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function webpage_update(&$content, $currentVersion)
{

    switch ($currentVersion) {
    case version_compare($currentVersion, '0.1.0', '<'):
        if(!wp_update_010($content)) {
            return FALSE;
        }

    case version_compare($currentVersion, '0.2.0', '<'):
        $content[] = '+ Add ability to join all pages together';
        $content[] = '+ Fixed xhtml issues with links';
        $content[] = '+ Front page should no longer pull unapproved pages.';

    case version_compare($currentVersion, '0.2.1', '<'):
        $content[] = '+ Added parseTags to content.';

    case version_compare($currentVersion, '0.2.2', '<'):
        $result = wp_update_022();
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = 'New - added active column to web page admin list';

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
        $result = $db->addTableColumn('active', 'smallint NOT NULL default \'0\'');
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

function wp_update_022()
{
    $files[] = 'templates/forms/list.tpl';
    $result = PHPWS_Boost::updateFiles($files, 'webpage');
    if (PEAR::isError($result)) {
        return $result;
    }

    $db = & new PHPWS_DB('webpage_volume');
    $result = $db->addTableColumn('active', 'smallint NOT NULL default \'1\'');
    if (PEAR::isError($result)) {
        return $result;
    }

    return true;
}


function wp_update_010(&$content)
{
    $db = & new PHPWS_DB('webpage_volume');
    $result = $db->addTableColumn('create_user_id', 'int NOT NULL default \'0\'', 'date_updated');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        return FALSE;
    }

    $db->addTableColumn('update_user_id', 'int NOT NULL default \'0\'', 'created_user');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        return FALSE;
    }
    $content[] = 'Added user id tracking columns to database tables.';

    $db->addTableColumn('approved', 'smallint NOT NULL default \'1\'', 'frontpage');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        return FALSE;
    }

    $content[] = 'Created approval hooks in volume table.';

    $db = & new PHPWS_DB('webpage_page');
    $db->addTableColumn('approved', 'smallint NOT NULL default \'1\'', 'template');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        return FALSE;
    }
    $content[] = 'Created approval hooks in page table.';

    return TRUE;
}

?>