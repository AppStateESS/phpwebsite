<?php

/**
 * Uninstall file for blog
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function blog_uninstall(&$content)
{
    Core\DB::dropTable('blog_entries');
    $content[] = dgettext('blog', 'Blog tables removed.');
    return TRUE;
}


?>
