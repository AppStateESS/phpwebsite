<?php

  /**
   * Uninstall file for profiles
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function profiles_uninstall(&$content)
{
    PHPWS_DB::dropTable('profiles');

    $content[] = _('Profiles table removed.');
    return TRUE;
}


?>
