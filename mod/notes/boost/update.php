<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function notes_update(&$content, $version) {

    switch ($version) {
    case version_compare($version, '0.1.0', '<'):
        PHPWS_Boost::registerMyModule('notes', 'users', $content);
        $db = & new PHPWS_DB('notes');
        $db->dropTableColumn('check_key');
        $db->addTableColumn('sender_id', 'int NOT NULL default \'0\'');
        $db->addTableColumn('read_once', 'smallint NOT NULL default \'0\'');
        $db->addTableColumn('encrypted', 'smallint NOT NULL default \'0\'');
        $db->addTableColumn('date_sent', 'int NOT NULL default \'0\'');
        $content[] = '+ Large portions rewritten.';
    }

    return true;
}

?>