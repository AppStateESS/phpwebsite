<?php
/**
 * This is the phpWsBB uninstall file for Boost
 *
 * @version $Id: uninstall.php,v 1.2 2008/09/12 07:12:12 adarkling Exp $
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 */
function phpwsbb_uninstall(&$content)
{
    PHPWS_DB::dropTable('phpwsbb_forums');
    PHPWS_DB::dropTable('phpwsbb_topics');
    PHPWS_DB::dropTable('phpwsbb_users');
    PHPWS_DB::dropTable('phpwsbb_moderators');
    $content[] = dgettext('phpwsbb', 'Bulletin Board tables removed.');
    return TRUE;
}

?>