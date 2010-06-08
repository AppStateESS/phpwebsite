<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function rss_uninstall()
{
    Core\DB::dropTable('rss_channel');
    Core\DB::dropTable('rss_feeds');
    return TRUE;
}

?>
