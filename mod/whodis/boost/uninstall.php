<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function whodis_uninstall()
{
    PHPWS_DB::dropTable('whodis');
    return true;
}

?>