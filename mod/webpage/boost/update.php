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
    }

    return TRUE;

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