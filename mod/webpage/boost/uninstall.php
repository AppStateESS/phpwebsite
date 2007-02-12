<?php

  /**
   * Uninstall file for webpage
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function webpage_uninstall(&$content)
{
    PHPWS_DB::dropTable('webpage_volume');
    PHPWS_DB::dropTable('webpage_page');
    PHPWS_DB::dropTable('webpage_featured');
    $content[] = _('Web Page tables removed.');
    return TRUE;
}

?>