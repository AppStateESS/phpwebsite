<?php

  /**
   * Uninstall file for blog
   */

function blog_uninstall(&$content)
{
    PHPWS_DB::dropTable('blog_entries');
    $content[] = _('Blog tables removed.');
    return TRUE;
}


?>
