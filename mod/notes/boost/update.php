<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function notes_update(&$content, $version) {

    switch ($version) {
    case version_compare($version, '0.1.0', '<'):
        $files = array();
        $files[] = 'conf/config.php';
        $files[] = 'templates/note.tpl';
        PHPWS_Boost::updateFiles($files, 'notes');
        PHPWS_Boost::registerMyModule('notes', 'users', $content);
        $db = & new PHPWS_DB('notes');
        $db->dropTableColumn('check_key');
        $db->addTableColumn('key_id', 'int NOT NULL default \'0\'');
        $db->addTableColumn('sender_id', 'int NOT NULL default \'0\'');
        $db->addTableColumn('read_once', 'smallint NOT NULL default \'0\'');
        $db->addTableColumn('encrypted', 'smallint NOT NULL default \'0\'');
        $db->addTableColumn('date_sent', 'int NOT NULL default \'0\'');
        $content[] = '+ Large portions rewritten.';

    case version_compare($version, '0.1.1', '<'):
        $content[] = 'Fix - Notes user entry was sending notes on unsuccessful username searches.';

    case version_compare($version, '0.1.2', '<'):
        PHPWS_Boost::registerMyModule('notes', 'users', $content);
        $content[] = 'New - Added "Delete and Close" button to read page.';
        $content[] = 'Fix - Notes now searches and shows display names by default.';
        $content[] = 'New - Added permission to search usernames as well.';
        $content[] = 'Fix - Search list user ids were broken.';
    }

    return true;
}

?>