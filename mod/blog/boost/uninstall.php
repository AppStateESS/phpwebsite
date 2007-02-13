<?php

  /**
   * Uninstall file for blog
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function blog_uninstall(&$content)
{
    translate('blog');
    PHPWS_DB::dropTable('blog_entries');
    $content[] = _('Blog tables removed.');
    translate();
    return TRUE;
}


?>
