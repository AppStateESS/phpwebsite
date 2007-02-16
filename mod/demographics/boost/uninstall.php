<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function demographics_uninstall(&$content)
{
    PHPWS_DB::dropTable('demographics');
    translate('demographics');
    $content[] = _('Demographics table removed.');
    translate('demographics');
    return TRUE;
}

?>