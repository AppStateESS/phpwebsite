<?php
  /**
   * @version $Id: uninstall.php 5472 2007-12-11 16:13:40Z jtickle $
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function pagesmith_uninstall(&$content)
{
//    PHPWS_DB::dropTable('ps_block');
    $content[] = 'Tables removed.';
    return TRUE;
}

?>
