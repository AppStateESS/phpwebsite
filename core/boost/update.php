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

    case version_compare($version, '1.0.5', '<'):
        $content[] = '- Fixed core version on installation.';
        $content[] = '- Changed Core.php and Module.php to track core\'s version better. Helps Boost with dependencies';

    case version_compare($version, '1.0.6', '<'):
        $content[] = '- Fixed locale cookie saving incorrectly.';

    case version_compare($version, '1.0.7', '<'):
        $content[] = '- Key.php : Added parameter to avoid home keys when calling getCurrent.';
        $content[] = '- Database.php : fixed a small bug with adding columns using "as". Value was carrying over to other columns.';
        $content[] = '- Form.php : Added an error check on a select value.';
        $content[] = '- Documentation : updated DB_Pager.txt with information on setting a column order.';
        $content[] = '- Init.php - Commented out putenv functions.';
        $content[] = '- Javascript : close_refresh - added option to not auto-close';

    case version_compare($version, '1.0.8', '<'):
        $content[] = '- Module.php : now adds error to _error variable if module could not be loaded.';
        
    }
    
    return true;
}


?>