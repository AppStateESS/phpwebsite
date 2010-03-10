<?php

/**
 * Uninstall file for blog
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function blog_uninstall(&$content)
{
    PHPWS_DB::dropTable('blog_entries');
    $content[] = dgettext('blog', 'Blog tables removed.');
    return TRUE;
}


?>
