<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function core_update(&$content, $version) {
    
    switch ($version) {
    case version_compare($version, '1.0.2', '<'):
    
        $db = & new PHPWS_DB('core_version');
        $db->addValue('version', 'varchar(10) NOT NULL default \'\'');
        $result = $db->createTable();
        if (PEAR::isError($result)) {
            return $result;
        }
        $db->reset();
        $db->addValue('version', '1.0.0');
        $result = $db->insert();
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '- Created core_version table.';

    case version_compare($version, '1.0.3', '<'):
        $content[] = 'Please see changes_1_0_3.txt in the core\'s boost directory for a listing of changes.';
        break;
    }
    
    return true;
}


?>