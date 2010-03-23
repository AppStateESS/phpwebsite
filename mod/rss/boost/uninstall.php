<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rss_uninstall()
{
    PHPWS_DB::dropTable('rss_channel');
    PHPWS_DB::dropTable('rss_feeds');
    return TRUE;
}

?>
