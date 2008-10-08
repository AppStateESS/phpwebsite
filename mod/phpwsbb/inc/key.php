<?php
/**
 * unregisters deleted keys from phpwsbb
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @version $Id: key.php,v 1.1 2008/08/23 04:19:16 adarkling Exp $
 */


function phpwsbb_unregister_key(&$key)
{
    if (empty($key) || empty($key->id)) {
        return FALSE;
    }

	$db = & new PHPWS_DB('phpwsbb_topics');
	$db->addWhere('id', $key->item_id);
	return $db->delete();
}

?>