<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function whodis_uninstall()
{
    PHPWS_DB::dropTable('whodis');
    PHPWS_DB::dropTable('whodis_filters');
    return true;
}

?>
