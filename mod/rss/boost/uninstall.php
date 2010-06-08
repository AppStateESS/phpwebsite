<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rss_uninstall()
{
    \core\DB::dropTable('rss_channel');
    \core\DB::dropTable('rss_feeds');
    return TRUE;
}

?>
